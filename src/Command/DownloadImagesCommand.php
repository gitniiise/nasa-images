<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Console\Question\ConfirmationQuestion;


#[AsCommand(
    name: 'app:download-images',
    description: 'Downloads nasa images (of a given day) and puts them into a given folder',
)]
class DownloadImagesCommand extends Command
{

    /**
     * Constructs a new instance of DownloadImagesCommand.
     *
     * Initializes the DownloadImagesCommand by calling the parent constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Configures the current command.
     *
     * Sets the name and description of the command and defines the required and optional arguments.
     * - Command Name: app:download-images
     * - Description: Downloads images from NASA EPIC API
     * - Required Argument: 'target-folder' - Target folder for images
     * - Optional Argument: 'date' - Date for images (optional)
     *
     * @return void
     */
    protected function configure(): void
    {
       $this->setName('app:download-images')
            ->setDescription('Downloads images from NASA EPIC API')
            ->addArgument('target-folder', InputArgument::REQUIRED, 'Target folder for images')
            ->addArgument('date', InputArgument::OPTIONAL, 'Date for images (optional)');
    }

    /**
     * Creates or handles the subfolder for images.
     *
     * This method is responsible for managing the subfolder for storing downloaded images. 
     * It checks if the target folder is writable; if not, it displays an error message and exits.
     * If the subfolder already exists, it prompts the user for confirmation to overwrite. 
     * If confirmed, it overwrites the folder; if not, the process is aborted. 
     * If the subfolder doesn't exist, it creates the directory.
     *
     * @param string           $targetFolder The path to the target folder.
     * @param string           $date         The date for images (used to create subfolder name).
     * @param InputInterface   $input        The InputInterface object for interacting with the command input.
     * @param OutputInterface  $output       The OutputInterface object for displaying messages.
     * @return string                        The subfolder path or Command::SUCCESS upon successful user confirmation.
     */
    protected function createSubfolder(string $targetFolder, string $date, InputInterface $input, OutputInterface $output): string
    {
        if (!is_writable($targetFolder)) {
            $output->writeln('Please make sure the directory exists and you have the necessary permissions.');
            return Command::FAILURE;
        }
        $subfolder = $targetFolder . '/' . $date;

        if (is_dir($subfolder)) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                'The target folder already exists. Do you want to overwrite it? (yes/no) ',
                false
            );

            if ($helper->ask($input, $output, $question)) {
                $output->writeln('Overwriting the folder.');

            } else {
                $output->writeln('The process was aborted by user.');
                return Command::FAILURE;
            }
        } else {
            mkdir($subfolder, 0755, true);
        }
        return $subfolder;
    }

    /**
     * Makes an HTTP request to the NASA EPIC API.
     *
     * This method constructs and sends an HTTP GET request to the NASA EPIC API
     * to retrieve images for a given date using the provided API key. It handles
     * various exceptions that may occur during the request and returns the response
     * object if the request is successful or null otherwise.
     *
     * @param string           $date       The date for which images are requested.
     * @param string           $apiKey     The API key required to access the NASA EPIC API.
     * @param OutputInterface  $output     The OutputInterface object for displaying messages.
     * @param HttpClient       $httpClient The HttpClient object for making HTTP requests.
     * @return Response|null               The response object if successful, null on failure.
     */
    private function makeHttpRequest($date, $apiKey, $output, $httpClient)
    {
        $apiUrl = sprintf('https://api.nasa.gov/EPIC/api/natural/date/%s?api_key=%s', $date, $apiKey);

        try {
            $response = $httpClient->request('GET', $apiUrl);

            if ($response->getStatusCode() !== 200) {
                $output->writeln('Failed to fetch images from the API.');
                return null;
            }
            return $response;
        } catch (ClientException $exception) {
            $output->writeln('HTTP error occurred: ' . $exception->getMessage());
            return null;
        } catch (\Exception $exception) {
            $output->writeln('An error occurred: ' . $exception->getMessage());
            return null;
        }
    }

    /**
     * Executes the 'app:download-images' command.
     *
     * This method is the entry point for the 'app:download-images' command execution.
     * It handles the process of downloading images from the NASA EPIC API based on the
     * specified date or the current date. It verifies the date format, retrieves image data,
     * saves the images to the target directory, and displays appropriate messages based on
     * the success or failure of the download process.
     *
     * @param InputInterface  $input  The InputInterface object containing command inputs.
     * @param OutputInterface $output The OutputInterface object for displaying messages.
     * @return int                    The status code (SUCCESS or FAILURE) after command execution.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dateInput = $input->getArgument('date');
        $dateObject = \DateTime::createFromFormat('Y-m-d', $dateInput ?? '');

        if ($dateObject === false && $dateInput !== null) {
            $output->writeln('Invalid date format. Please use the format YYYY-MM-DD.');
            return Command::FAILURE;
        }

        $date = $dateObject ? $dateObject->format('Y-m-d') : date('Y-m-d');

        $apiKey = $_ENV['NASA_API_KEY'];
        $httpClient = HttpClient::create();
        
        try {
            $response = $this->makeHttpRequest($date, $apiKey, $output, $httpClient);
            
            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();
                if ($dateInput == null) {
                    while(empty($data)) {
                        $date = date('Y-m-d', strtotime($date . ' -1 day'));
                        $response = $this->makeHttpRequest($date, $apiKey, $output, $httpClient);
                        $data = $response->toArray();
                    }
                    $output->writeln('Pictures from the last available day are from ' . $date);
                }
                $imageCount = count($data);
                if ($imageCount === 0) {
                    $output->writeln('There are no pictures for this point in time.');
                    return Command::FAILURE;
                }
                
                $targetFolder = $input->getArgument('target-folder');
                $subfolder = $this->createSubfolder($targetFolder, $date, $input, $output);
                if($subfolder == 1){
                    return Command::FAILURE;
                }
                $output->writeln('Downloading ' . $imageCount . ' images from ' . $date);
                
                foreach ($data as $image) {
                    $baseUrl = 'https://api.nasa.gov/EPIC/archive/natural/';
                    $imageDate = str_replace('-', '/', substr($image['date'], 0, 10)); // Extract date from image data
                    $imageName = $image['image'] . '.png';
                    $imageName = str_replace('-', '/', $imageName);
                    $imageUrl = "{$baseUrl}{$imageDate}/png/{$imageName}?api_key={$apiKey}";
                    
                    // Download the image
                    $imageData = $httpClient->request('GET', $imageUrl)->getContent();

                    if ($imageData) {
                        // Save the image in the subfolder with the date as filename
                        $saveResult = file_put_contents($subfolder . '/' . $imageName, $imageData);
                        $output->writeln('-> Successfully save image ' . $imageName);

                        if ($saveResult === false) {
                            $output->writeln('Failed to save image: ' . $imageDate);
                        }
                    } else {
                        $output->writeln('Failed to fetch images from the API. Status code: ' . $response->getStatusCode());
                        return Command::FAILURE;
                    }
                }
                $output->writeln('Images downloaded and saved successfully.');
                return Command::SUCCESS;
            } else {
                $output->writeln('Failed to fetch images from the API.');
                return Command::FAILURE;
            }
        } catch (ClientException $exception) {
            $output->writeln('HTTP error occurred: ' . $exception->getMessage());
            return Command::FAILURE;
        } catch (\Exception $exception) {
            $output->writeln('An error occurred: ' . $exception->getMessage());
            return Command::FAILURE;
        }
    }
}

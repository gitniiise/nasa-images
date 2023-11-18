<?php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\DownloadImagesCommand;
use Symfony\Component\HttpClient\HttpClient;

class DownloadImagesCommandTest extends KernelTestCase
{
    private $application;

    /**
     * Set up the testing environment before each test method execution.
     *
     * This method is part of PHPUnit's setup process for test classes. It initializes the necessary components
     * required for testing a Symfony command. It boots the kernel, creates a Symfony Console application, adds
     * the 'DownloadImagesCommand' to the application, and ensures the existence of a directory ('public/images')
     * for storing downloaded images with appropriate permissions.
     *
     * @return void
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $this->application = new Application('DownloadImagesCommand');
        $this->application->add(new DownloadImagesCommand());

        mkdir('public/images', 0755, true);
    }

    /**
     * Clean-up activities after each test case.
     * Deletes the directory for images after the completion of a test.
     * @return void
     */
    protected function tearDown(): void
    {
        $this->deleteDirectory('public/images');
    }

    /**
     * Recursively deletes a directory and its contents.
     *
     * @param string $path The path of the directory to be deleted.
     * @return void
     */
    private function deleteDirectory($path): void
    {
        if (is_dir($path)) {
            $objects = scandir($path);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($path . DIRECTORY_SEPARATOR . $object) && !is_link($path . DIRECTORY_SEPARATOR . $object)) {
                        $this->deleteDirectory($path . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($path . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            rmdir($path);
        }
    }

    /**
     * Tests the execution of the 'app:download-images' command.
     * Executes the command with specified parameters and asserts if the output contains
     * the success message confirming the images were downloaded and saved successfully.
     * @return void
     */
    public function testExecute(): void
    {
        $command = $this->application->find('app:download-images');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'target-folder' => 'public/images', 
            'date' => '2023-11-15', 
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Images downloaded and saved successfully.', $output);
    }

    /**
     * Tests the 'app:download-images' command execution with an invalid date format.
     * Executes the command with an invalid date parameter and asserts if the output contains
     * the error message indicating an invalid date format.
     * @return void
     */
    public function testExecuteInvalidDate(): void
    {
        $command = $this->application->find('app:download-images');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'target-folder' => 'public/images', 
            'date' => 'invalid-date-format', 
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Invalid date format. Please use the format YYYY-MM-DD.', $output);
    }

    /**
     * Tests the successful execution of the 'app:download-images' command.
     * Executes the command with a valid date and target folder, then asserts 
     * the command output contains a success message. It further verifies the 
     * existence of the downloaded images in the specified folder for the given date.
     * @return void
     */
    public function testExecuteSuccessfulDownload(): void
    {
        $command = $this->application->find('app:download-images');
        $commandTester = new CommandTester($command);

        $targetFolder = 'public/images';
        $date = '2023-11-15';

        $commandTester->execute([
            'command' => $command->getName(),
            'target-folder' => $targetFolder,
            'date' => $date,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Images downloaded and saved successfully.', $output);

        $this->assertDirectoryExists($targetFolder . '/' . $date);

        $files = glob($targetFolder . '/' . $date . '/*.png');
        $this->assertNotEmpty($files);
    }

    /**
     * Tests the scenario when no images are available for download for the specified date.
     * Executes the 'app:download-images' command with a past date where no images exist,
     * then asserts that the command output contains a message indicating the absence of images.
     * It further verifies that no directory is created for the specified date in the target folder.
     * @return void
     */
    public function testExecuteNoImagesAvailable(): void
    {
        $command = $this->application->find('app:download-images');
        $commandTester = new CommandTester($command);

        $targetFolder = 'public/images';
        $date = '2013-11-20';

        $commandTester->execute([
            'command' => $command->getName(),
            'target-folder' => $targetFolder,
            'date' => $date,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('There are no pictures for this point in time.', $output);

        $this->assertDirectoryDoesNotExist($targetFolder . '/' . $date);
    }

    /**
     * Tests the confirmation to overwrite an existing folder.
     * 
     * This method simulates a user confirming the overwrite action
     * when a folder already exists for the given date. It sets the input
     * to 'yes' to allow the command to overwrite the existing folder.
     * The test asserts that the output contains the confirmation message.
     * @return void
     */
    public function testOverwriteConfirmation(): void
    {
        $command = $this->application->find('app:download-images');
        $commandTester = new CommandTester($command);

        $targetFolder = 'public/images';
        $date = '2022-11-20';
        
        mkdir($targetFolder . '/' . $date, 0755, true);

        $question = "yes\n";
        $commandTester->setInputs([$question]);

        $commandTester->execute([
            'command' => $command->getName(),
            'target-folder' => $targetFolder,
            'date' => $date,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Overwriting the folder.', $output);
    }

   /**
     * Tests the scenario when a user chooses not to overwrite an existing folder.
     * 
     * This method simulates a user choosing not to overwrite an existing folder
     * when prompted by the command. It sets the input to 'no' to prevent the command
     * from overwriting the existing folder. The test asserts that the output contains
     * the message indicating the process was aborted and that the folder still exists.
     */
    public function testDontOverwriteConfirmation(): void
    {
        $command = $this->application->find('app:download-images');
        $commandTester = new CommandTester($command);

        $targetFolder = 'public/images';
        $date = '2021-11-03';
        
        mkdir($targetFolder . '/' . $date, 0755, true);

        $question = "no\n";
        $commandTester->setInputs([$question]);

        $commandTester->execute([
            'command' => $command->getName(),
            'target-folder' => $targetFolder,
            'date' => $date,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('The target folder already exists. Do you want to overwrite it? (yes/no) ', $output);
        $this->assertStringContainsString('The process was aborted by user.', $output);
        $this->assertDirectoryExists($targetFolder . '/' . $date);
    }

    /**
     * Tests handling an unwritable target folder for image downloads.
     *
     * This method validates the behavior of the 'app:download-images' command when
     * the target folder specified for image downloads is unwritable. It simulates an
     * unwritable folder by creating it with minimal permissions. The test executes
     * the command and asserts that the output contains a specific error message related
     * to the directory's unwritability. After the test, it removes the created unwritable
     * folder to maintain the testing environment.
     *
     * @return void
     */
    public function testUnwritableTargetFolder(): void
    {
        $command = $this->application->find('app:download-images');
        $commandTester = new CommandTester($command);

        $targetFolder = 'public/unwritable_folder';

        mkdir($targetFolder, 0444, true); 

        $commandTester->execute([
            'command' => $command->getName(),
            'target-folder' => $targetFolder,
            'date' => '2023-05-01',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Please make sure the directory exists and you have the necessary permissions.', $output);

        rmdir($targetFolder);
    }
}

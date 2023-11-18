<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use App\Command\DownloadImagesCommand;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureCommands(SymfonyApplication $application)
    {
        // ...

        $application->add(new DownloadImagesCommand());

        // ...
    }
}

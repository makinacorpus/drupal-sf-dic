<?php

namespace MakinaCorpus\Drupal\Sf\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Cats everywhere!
 *
 * This needs some polishing.
 *
 * @codeCoverageIgnore
 */
class GiveMeCatsCommand extends DrupalCommand
{
    const DEFAULT_WIDTH = 1920;
    const DEFAULT_HEIGHT = 1080;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('drupal:give-me-cats')
            ->setAliases(['cats'])
            ->setDescription('Finds all missing images files from the {file_managed} table and replaces them with placeholder images')
            ->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Where to store temporary images', 'temporary://give-me-cats')
            ->addOption('skip-download', 's', InputOption::VALUE_NONE, 'If set, use files within the directory, do not download new ones')
            //->addOption('force-download', 'f', InputOption::VALUE_NONE, 'Force download even if directory already exists')
            ->addOption('count', 'c', InputOption::VALUE_REQUIRED, 'Number of different images to fetch', 30)
            ->addOption('cat', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Categories of images, valid choices are those from http://lorempixel.com/', ['cats', 'animals', 'technics', 'people', 'nature'])
            //->addOption('no-grey', 'g', InputOption::VALUE_NONE, 'Set this to allow colorful images')
        ;
    }

    private function getDefaultCategories()
    {
        return ['cats', 'animals', 'technics', 'people', 'nature'];
    }

    private function aggregateImages(OutputInterface $output, $count, $directory, array $categories = [], $grey = false)
    {
        $output->writeln(sprintf("<info>%d images will be downloaded in %s</info>", $count, $directory));

        if (!\is_dir($directory)) {
            if (!\file_prepare_directory($directory, \FILE_CREATE_DIRECTORY | \FILE_MODIFY_PERMISSIONS)) {
                throw new \Exception(\sprintf("%s: cannot create directory", $directory));
            }
        }
        $directory = \drupal_realpath($directory);

        if (!$categories) {
            $categories = $this->getDefaultCategories();
        }

        $ret = [];

        $progressBar = new ProgressBar($output);
        $progressBar->start($count);

        $catCount = count($categories);
        for ($i = 0; $i < $count; ++$i) {

            $category = $categories[$i % $catCount];
            $width    = [768, 900, 1080, 1200, 1900][\rand(0, 4)];
            $height   = [768, 900, 1080, 1200, 1900][\rand(0, 4)];
            $filename = $directory.'/foo-'.$category.'-'.$i;
            $mimetype = $this->downloadFile($filename, $width, $height, $category, $grey);

            switch ($mimetype) {
                case 'image/jpeg':
                    $target = $filename.'.jpg';
                    break;
                case 'image/png':
                    $target = $filename.'.png';
                    break;
                default:
                    $target = $filename;
                    break;
            }

            if ($target !== $filename) {
                if (!\file_unmanaged_move($filename, $target)) {
                    throw new \Exception(sprintf("could not move %s to %s", $filename, $target));
                }
            }

            $ret[] = [$target, $mimetype];
            $progressBar->advance();
        }

        $progressBar->finish();

        return $ret;
    }

    private function getDefaultCurlOtpions()
    {
        return [
            \CURLOPT_FAILONERROR    => true,
            \CURLOPT_MAXREDIRS      => 5,
            \CURLOPT_TIMEOUT        => 30,
            \CURLOPT_SSL_VERIFYHOST => 2,
            \CURLOPT_SSL_VERIFYPEER => true,
            \CURLOPT_FAILONERROR    => false,
            \CURLOPT_FOLLOWLOCATION => true,
        ];
    }

    private function buildUrl($width, $height, $category = null, $grey = true)
    {
        /*
        $parts = ['http://lorempixel.com'];
        if ($grey) {
            $parts[] = 'g';
        }
        $parts[] = $width;
        $parts[] = $height;
        if ($category) {
            $parts[] = $category;
        }
         */

        $parts = ['http://placekitten.com'];
        if ($grey) {
            $parts[] = 'g';
        }
        $parts[] = $width;
        $parts[] = $height;

        return \implode('/', $parts);
    }

    /**
     * Download a new file and return the content mime type
     */
    private function downloadFile($filename, $width, $height, $category, $grey = true)
    {
        $output = \fopen($filename, "ab+");

        if (false === $output) {
            throw new \Exception(\sprintf("%s: unable to open file for writing", $filename));
        }

        // Init cURL
        $url = $this->buildUrl($width, $height, $category, $grey);
        $handle = \curl_init($url);
        foreach ($this->getDefaultCurlOtpions() as $opt => $value) {
            \curl_setopt($handle, $opt, $value);
        }

        // Set Drupal proxy if set
        if ($proxy = \variable_get('proxy_server')) {
            if ($proxyPort = \variable_get('proxy_port')) {
                $proxy .= ':'.$proxyPort;
            }
            if ($proxyUser = \variable_get('proxy_username', '')) {
                $proxyPass = \variable_get('proxy_password', '');
                $proxy = $proxyUser.':'.$proxyPass.'@'.$proxy;
            }
            \curl_setopt($handle, CURLOPT_PROXY, $proxy);
        }

        // Write return to file
        \curl_setopt($handle, CURLOPT_FILE, $output);
        \curl_exec($handle);

        // Handle gracefully errors if any
        $httpCode = \curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if ($httpCode < 100 || $httpCode >= 400) {
            $errno = \curl_errno($handle);
            $err = \curl_error($handle);
            \curl_close($handle);
            throw new \Exception(sprintf("%d: %s (HTTP status %d) while downloading %s", $errno, $err, $httpCode, $url));
        }

        \curl_close($handle);

        // @todo fixme
        return 'image/jpeg';
    }

    /**
     * Handle a single file
     */
    private function replaceFile(\DatabaseConnection $database, $item, $newfile, $newMimetype)
    {
        if ($wrapper = \file_stream_wrapper_get_instance_by_uri($item->uri)) {
            $filename =  DRUPAL_ROOT.'/'.$wrapper->getDirectoryPath().'/'.explode('://', $item->uri)[1];
        } else {
            $filename = $item->uri;
        }

        if (!\file_exists($filename)) {
            if (!\file_prepare_directory(\dirname($filename), \FILE_CREATE_DIRECTORY | \FILE_MODIFY_PERMISSIONS)) {
                throw new \Exception(sprintf("could not create directory: %s", \dirname($filename)));
            }
            if (!\file_unmanaged_copy($newfile, $filename, FILE_EXISTS_REPLACE)) {
                throw new \Exception(sprintf("could not copy %s on %s", $newfile, $filename));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $categories = $input->getOption('cat');
        $directory = $input->getOption('directory');
        $noDownload = (bool)$input->getOption('skip-download');
        //$grey = !$input->getOption('no-grey');
        $count = (int)$input->getOption('count');
        if (!$count || $count < 0) {
            $output->writeln("<error>--count parameter must be a valid positive integer</error>");
        }

        if (!$noDownload) {
            $sources = $this->aggregateImages($output, $count, $directory, $categories);
        }
        if (!$directory = \drupal_realpath($directory)) {
            throw new \Exception(sprintf("%s cannot find folder (realpath() call failed)"));
        }

        $sources = [];
        foreach (new \FilesystemIterator($directory, \FilesystemIterator::CURRENT_AS_PATHNAME) as $filename) {
            /** @var \SplFileInfo $file */
            $sources[] = [$filename, file_get_mimetype($filename)];
        }
        $sourcesCount = count($sources);

        /** @var \DatabaseConnection $database */
        $database = $this->getContainer()->get('database');

        $total = $database->query("SELECT COUNT(*) FROM {file_managed} WHERE filemime LIKE 'image/%'")->fetchField();
        $result = $database->query("SELECT * FROM {file_managed} WHERE filemime LIKE 'image/%' ORDER BY timestamp DESC, fid DESC");
        $progress = new ProgressBar($output);
        $progress->start($total);

        // This is PDO statement, so in theory, result will not be buffered
        // on our side, we should be able to safely work with large datasets.
        $index = 0;
        foreach ($result as $item) {
            list($newFile, $newMimetype) = $sources[$index % $sourcesCount];
            $this->replaceFile($database, $item, $newFile, $newMimetype);
            $index++;
            $progress->advance();
        }

        $progress->finish();
    }
}

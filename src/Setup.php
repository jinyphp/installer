<?php
/*
 * This file is part of the jinyPHP package.
 *
 * (c) hojinlee <infohojin@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Jiny\Installer;

use ZipArchive;
use GuzzleHttp\Client;
use Symfony\Component\Process\Process;

/**
 * jinyPHP 설치
 */
class Setup 
{
    CONST VERSION = "0.1.0";

    public function __construct($argv)
    {
        echo "jinyPHP Installer ".self::VERSION."\n";
        //echo __DIR__."\n";
        $this->cmd($argv);        
    }

    /**
     * 명령어를 분석합니다.
     */
    public function cmd($argv)
    {
        if ( isset($argv[1]) ) {
            switch ($argv[1]) {
                // 새로운 프로젝트를 설치합니다.
                case 'new':                    
                    if (isset($argv[2])) {
                        $this->newProject($argv[2]);
                    } else {
                        echo "프로젝트명을 입력해 주세요.\n";
                    }
                    break;
                //
                default:
            }

        } else {
            echo "명령을 입력하세요.\n";
        }
    }
    
    /**
     * 새로운 프로젝트를 설치합니다.
     */
    public function newProject($project)
    {
        echo "Creating new project\n";
        echo "download last version from git. please, wait time\n";
        //if (!is_dir($project)) mkdir($project);

        // 원본 파일을 다운로드 받습니다.
        $zipFile = "0.1.6.zip";
        //////$this->download($zipFile);

        // 압축 파일을 
        $directory = ".".DIRECTORY_SEPARATOR.$project.DIRECTORY_SEPARATOR;
        $this->extract($zipFile, $directory);

        // 파일을 삭제합니다.
        //////$this->cleanUp($zipFile);

        
        $composer = $this->findComposer();
        echo $composer;
        
        $commands = [
            $composer.' install --no-scripts',
            $composer.' run-script post-root-package-install',
            $composer.' run-script post-create-project-cmd',
            $composer.' run-script post-autoload-dump',
        ];

        $process = new Process(implode(' && ', $commands), $directory, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            $process->setTty(true);
        }

        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });

        $output->writeln('<comment>Application ready! Build something amazing.</comment>');

    

    }

    protected function download($zipFile, $version = 'master')
    {
        switch ($version) {
            case 'develop':
                $filename = 'latest-develop.zip';
                break;
            case 'master':
                // $filename = 'latest.zip';
                $filename = "https://github.com/jinyphp/jiny/archive/0.1.6.zip";
                break;
        }

        $response = (new Client)->get($filename);

        file_put_contents($zipFile, $response->getBody());


        return $this;
    }

    /**
     * Extract the Zip file into the given directory.
     *
     * @param  string  $zipFile
     * @param  string  $directory
     * @return $this
     */
    protected function extract($zipFile, $directory)
    {
        $archive = new ZipArchive;

        $archive->open($zipFile);

        $archive->extractTo(".");
        rename("jiny-0.1.6", $directory);

        $archive->close();

        return $this;
    }

    /**
     * Clean-up the Zip file.
     *
     * @param  string  $zipFile
     * @return $this
     */
    protected function cleanUp($zipFile)
    {
        @chmod($zipFile, 0777);

        @unlink($zipFile);

        return $this;
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        if (file_exists(getcwd().'/composer.phar')) {
            return '"'.PHP_BINARY.'" composer.phar';
        }

        return 'composer';
    }

}
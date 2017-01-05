<?php

namespace Melihovv\LaravelLogViewer;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use InvalidArgumentException;

class LaravelLogViewer
{
    /**
     * Base directory.
     * @var string
     */
    protected $baseDir;

    /**
     * Current directory.
     * @var string
     */
    protected $currentDir;

    /**
     * Current file.
     * @var string
     */
    protected $currentFile;

    /**
     * Max file size.
     * @var int
     */
    protected $maxFileSize;

    protected static $levelsClasses = [
        'debug' => 'info',
        'info' => 'info',
        'notice' => 'info',
        'warning' => 'warning',
        'error' => 'danger',
        'critical' => 'danger',
        'alert' => 'danger',
        'emergency' => 'danger',
    ];

    protected static $levelsImgs = [
        'debug' => 'info',
        'info' => 'info',
        'notice' => 'info',
        'warning' => 'warning',
        'error' => 'warning',
        'critical' => 'warning',
        'alert' => 'warning',
        'emergency' => 'warning',
    ];

    /**
     * @param string $baseDir
     * @param int $maxFileSize
     */
    public function __construct($baseDir, $maxFileSize)
    {
        $this->baseDir = $baseDir;
        $this->currentDir = $baseDir;
        $this->maxFileSize = $maxFileSize;
    }

    /**
     * Returns logs from current file.
     * @return array|null
     */
    public function getLogsFromCurrentFile()
    {
        if ($this->currentFile === null) {
            return [];
        }

        if (File::size($this->currentFile) > $this->maxFileSize) {
            return null;
        }

        $datePattern = '\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}';
        $pattern = "/\\[$datePattern\\].*/";
        $fileContent = File::get($this->currentFile);

        preg_match_all($pattern, $fileContent, $rows);

        if (!is_array($rows) || count($rows) === 0) {
            return [];
        }

        $rows = $rows[0];
        $logs = [];

        foreach ($rows as $row) {
            preg_match(
                "/^\\[($datePattern)\\].*?(\\w+)\\."
                . '([A-Z]+): (.*?)( in .*?:[0-9]+)?$/',
                $row,
                $matches
            );

            if (!isset($matches[4])) {
                continue;
            }

            $level = Str::lower($matches[3]);

            $inFile = null;
            if (isset($matches[5])) {
                $inFile = substr($matches[5], 4);
            }

            $logs[] = (object)[
                'context' => $matches[2],
                'level' => $level,
                'levelClass' => static::$levelsClasses[$level],
                'levelImg' => static::$levelsImgs[$level],
                'date' => $matches[1],
                'text' => trim($matches[4]),
                'inFile' => $inFile,
            ];
        }

        return array_reverse($logs);
    }

    /**
     * Returns content (files and folders) of current folder.
     * @return array
     */
    public function getCurrentDirectoryContent()
    {
        $content = File::glob("$this->currentDir/*");

        $content = array_map(function ($item) {
            return (object)[
                'path' => $this->getPathRelativeToBaseDir($item),
                'name' => Str::substr(
                    $item,
                    Str::length($this->currentDir) + 1
                ),
                'isFile' => File::isFile($item),
                'isDir' => File::isDirectory($item),
            ];
        }, $content);

        return $content;
    }

    /**
     * @return string
     */
    public function getBaseDirectory()
    {
        return $this->baseDir;
    }

    /**
     * @param string $baseDir
     * @return $this
     */
    public function setBaseDirectory($baseDir)
    {
        $this->baseDir = $baseDir;

        return $this;
    }

    /**
     * Absolute path to current directory.
     * @return string
     */
    public function getCurrentDirectory()
    {
        return $this->currentDir;
    }

    /**
     * @return string
     */
    public function getCurrentDirectoryRelativeToBaseDir()
    {
        return $this->getPathRelativeToBaseDir($this->currentDir);
    }

    /**
     * @param string $directory Relative path to directory from base path.
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setCurrentDirectory($directory)
    {
        $directory = $this->normalizePath("$this->baseDir/$directory");

        $this->checkIfPathInBaseDir($directory);

        $this->currentDir = $directory;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentFile()
    {
        return $this->currentFile;
    }

    /**
     * @return string
     */
    public function getCurrentFileRelativeToBaseDir()
    {
        return $this->getPathRelativeToBaseDir($this->currentFile);
    }

    /**
     * Returns relative path to base directory.
     * @param string $path Absolute path.
     * @return string
     */
    protected function getPathRelativeToBaseDir($path)
    {
        return Str::substr($path, Str::length($this->baseDir));
    }

    /**
     * @param string $file Relative path to file from base directory.
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setCurrentFile($file)
    {
        $file = $this->normalizePath("$this->baseDir/$file");

        $this->checkIfPathInBaseDir($file);
        $this->currentFile = $file;

        return $this;
    }

    /**
     * Checks if passed path is inside base directory.
     * @param string $path Absolute path.
     * @throws InvalidArgumentException
     */
    protected function checkIfPathInBaseDir($path)
    {
        if (!Str::startsWith($path, $this->baseDir)) {
            throw new InvalidArgumentException(
                "Passed directory is not in base directory $this->baseDir"
            );
        }
    }

    /**
     * Normalizes path.
     * @param string $path Absolute path.
     * @return string Normalized path.
     * @throws InvalidArgumentException
     */
    protected function normalizePath($path)
    {
        $path = realpath($path);

        if ($path === false) {
            throw new InvalidArgumentException('Not existing path');
        }

        return $path;
    }

    /**
     * Returns path to parent of current directory.
     * @return string
     */
    public function getRelativePathToCurrentDirectoryParent()
    {
        if ($this->baseDir === $this->currentDir) {
            return DIRECTORY_SEPARATOR;
        }

        $path = realpath($this->currentDir . DIRECTORY_SEPARATOR . '..');

        return $this->getPathRelativeToBaseDir($path) ?: DIRECTORY_SEPARATOR;
    }

    /**
     * Returns true if current directory is also base directory.
     * @return bool
     */
    public function isCurrentDirectoryBase()
    {
        return $this->currentDir === $this->baseDir;
    }
}

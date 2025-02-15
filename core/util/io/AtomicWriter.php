<?php

namespace fmr\util\io;

use fmr\system\exception\SystemException;
use fmr\util\FileUtil;

class AtomicWriter extends File
{
    /**
     * The file the data should be flushed into.
     * @var string
     */
    protected $targetFilename = '';

    /**
     * AtomicWriter will be unusable, once this flag is true.
     * @var bool
     */
    protected $isFlushed = false;

    /**
     * Opens a new file. The file is always opened in binary mode.
     *
     * @param string $filename
     * @throws  SystemException
     */
    public function __construct($filename)
    {
        $this->targetFilename = $filename;

        $i = 0;
        while (true) {
            try {
                parent::__construct(FileUtil::getTemporaryFilename('atomic_'), 'xb');
                break;
            } catch (SystemException $e) {
                // allow at most 5 failures
                if (++$i === 5) {
                    throw $e;
                }
            }
        }

        if (!\flock($this->resource, \LOCK_EX)) {
            throw new SystemException('Could not get lock on temporary file');
        }
    }

    /**
     * @inheritDoc
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Closes the file, while discarding any written data, noop if the
     * file is already closed or flushed.
     */
    public function close()
    {
        if (!$this->isFlushed) {
            $this->isFlushed = true;

            \flock($this->resource, \LOCK_UN);
            \fclose($this->resource);
            @\unlink($this->filename);
        }
    }

    /**
     * Persists the written data into the target file. The flush is atomic
     * if the underlying storage supports an atomic rename.
     */
    public function flush() {
        $this->isFlushed = true;

        fflush($this->resource);
        flock($this->resource, \LOCK_UN);
        fclose($this->resource);

        $i = 0;
        while (true) {
            try {
                \rename($this->filename, $this->targetFilename);
                break;
            } catch (SystemException $e) {
                if (++$i === 5) {
                    throw $e;
                }

                usleep(random_int(0, .1e6)); // 0 to .1 seconds
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function __call($function, $arguments) {
        if ($this->isFlushed) {
            throw new SystemException('AtomicWriter for ' . $this->targetFilename . ' was already flushed.');
        }

        switch ($function) {
            case 'write':
            case 'puts':
            case 'seek':
            case 'tell':
            case 'rewind':
            case 'truncate':
                // these are fine
                break;
            default:
                throw new SystemException("AtomicWriter does not allow '" . $function . "'");
        }

        return parent::__call($function, $arguments);
    }
}
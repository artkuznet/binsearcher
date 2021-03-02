<?php

declare(strict_types=1);

class BinSearcher
{
    /**
     * @var resource
     */
    private $res;
    /**
     * @var int
     */
    private $fileSize;
    /**
     * @var int
     */
    private $bufferSize = 1024;

    public function __construct(string $fileName)
    {
        if (file_exists($fileName)) {
            $this->fileSize = filesize($fileName);
            $this->res = fopen($fileName, 'r');
        }
    }

    public function __destruct()
    {
        if ($this->res) {
            fclose($this->res);
        }
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function GetValue(string $key): ?string
    {
        if (!$this->res) {
            return null;
        }
        $div = 2;
        $prevOffset = -1;
        $offset = (int) round($this->fileSize / $div);
        while ($prevOffset !== $offset) {
            $start = $this->GetOffset($offset, 0x0A);
            if (!$start) {
                return null;
            }
            $end = $this->GetOffset($start, 0x09);
            if (!$end) {
                return null;
            }
            fseek($this->res, $start);
            $div *= 2;
            $compare = strnatcmp(fread($this->res, $end - $start), $key);
            if ($compare === 0) {
                $endValue = $this->GetOffset($end, 0x0A);
                if (!$endValue) {
                    return null;
                }
                fseek($this->res, $end);

                return fread($this->res, $endValue - $end);
            } else {
                $prevOffset = $offset;
                $offset += (int) (round($this->fileSize / $div) * $compare * -1);
            }
        }

        return null;
    }

    /**
     * @param int $offset
     * @param int $char
     * @return bool|int
     */
    private function GetOffset(int $offset, int $char)
    {
        fseek($this->res, $offset);
        while (!feof($this->res)) {
            $position = strpos(fread($this->res, $this->bufferSize), $char);
            if ($position) {
                return $offset + $position;
            }
            $offset += $this->bufferSize;
        }

        return false;
    }
}
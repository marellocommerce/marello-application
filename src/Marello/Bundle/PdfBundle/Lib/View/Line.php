<?php

namespace Marello\Bundle\PdfBundle\Lib\View;

class Line implements \ArrayAccess
{
    protected $fields = [];

    protected $height = 1;

    public function __construct(array $availableFields = [])
    {
        foreach ($availableFields as $field) {
            $this->fields[$field] = [null];
        }
    }

    public function offsetExists($key)
    {
        return isset($this->fields[$key]) || array_key_exists($key, $this->fields);
    }

    public function offsetGet($key)
    {
        if ($this->offsetExists($key) === false) {
            throw $this->createInvalidArgumentException($key);
        }

        return $this->fields[$key];
    }

    public function offsetSet($key, $value)
    {
        if ($this->offsetExists($key) === false) {
            throw $this->createInvalidArgumentException($key);
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        $this->fields[$key] = $value;
        $this->recalculateHeight();
    }

    public function offsetUnset($key)
    {
        if ($this->offsetExists($key) === false) {
            throw $this->createInvalidArgumentException($key);
        }

        $this->fields[$key] = [null];
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getDisplayLines()
    {
        $lines = [];
        for ($i = 0; $i < $this->height; $i++) {
            $line = [];

            foreach (array_keys($this->fields) as $key) {
                $line[$key] = $this->fields[$key][$i] ?? null;
            }

            $lines[] = $line;
        }

        return $lines;
    }

    public function isEmpty()
    {
        foreach ($this->fields as $field) {
            if (count($field) > 1 || reset($field) !== null) {
                return false;
            }
        }

        return true;
    }

    protected function recalculateHeight()
    {
        $this->height = max(array_map('count', $this->fields));
    }

    private function createInvalidArgumentException($key)
    {
        return new \InvalidArgumentException(sprintf(
            'Key "%s" does not exist in %s object with keys %s',
            $key,
            __CLASS__,
            implode(', ', array_keys($this->fields))
        ));
    }
}

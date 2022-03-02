<?php

namespace Storekeeper;

class Writer
{
    protected int $space = 4;
    protected int $level = 1;

    /**
     * PHP config code
     * @param array $config
     * @return string PHP code
     */
    public function write(array $config): string
    {
        return
            '<?php ' . PHP_EOL
            . 'return [' . PHP_EOL
            . $this->parseArray($config)
            . '];';
    }

    /**
     * Transform value to valid form
     * @param mixed $value
     * @return string Valid value
     */
    protected function value($value): string
    {
        // '...' => [         <----- (1 level)
        //     '...' => '...' <----- (+1 level)
        // ]                  <----- (-1 level)
        if (is_array($value)) {
            $this->level++;

            $value = '[' . PHP_EOL . $this->parseArray($value);

            $this->level--;
            $value .= $this->whitespace() . ']';
        }
        else if (is_string($value)) $value = "'$value'";
        else if (is_bool($value)) $value = $value ? 'true' : 'false';

        $value .= ',' . PHP_EOL;

        return $value;
    }

    /**
     * Transform key to valid form
     * @param string $key
     * @return string
     */
    protected function key(string $key): string
    {
        return "'$key'";
    }

    /**
     * @return string Calculated whitespace
     */
    protected function whitespace(): string
    {
        return str_repeat(' ', $this->space * $this->level);
    }

    /**
     * Transform array into PHP code
     * @param array $array
     * @return string PHP code
     */
    protected function parseArray(array $array): string
    {
        $output = '';
        foreach ($array as $key => $value) {
            $output .=  $this->whitespace() . $this->key($key) . ' => ' . $this->value($value);
        }

        return $output;
    }
}

<?php

namespace itsmng\Database\Schema;

class SchemaFingerprint
{
    public function hash(array $schema): string
    {
        return sha1(json_encode($this->normalize($schema), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    private function normalize(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        $normalized = [];
        if (array_is_list($value)) {
            foreach ($value as $item) {
                $normalized[] = $this->normalize($item);
            }

            return $normalized;
        }

        ksort($value);
        foreach ($value as $key => $item) {
            $normalized[$key] = $this->normalize($item);
        }

        return $normalized;
    }
}

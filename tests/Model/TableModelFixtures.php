<?php

namespace MUtilTest\Model;

use MUtilTest\Test\FixturesInterface;

class TableModelFixtures implements FixturesInterface
{
    public function getData(): array
    {
        return [
            't1' => [
                [
                    'id' => '1',
                    'c1' => 'col1-1',
                    'c2' => 'col2-1',
                ],
            ],
        ];
    }
}
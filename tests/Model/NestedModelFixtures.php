<?php

namespace MUtilTest\Model;

use MUtilTest\Test\FixturesInterface;

class NestedModelFixtures implements FixturesInterface
{
    public function getData(): array
    {
        return [
            'n1' => [
                [
                    'id' => 1,
                    'c1' => 'col1-1',
                    'c2' => 'col2-1',
                ],
                [
                    'id' => 2,
                    'c1' => 'col1-2',
                    'c2' => 'col2-2',
                ],
                [
                    'id' => 3,
                    'c1' => 'col1-3',
                    'c2' => 'col2-3',
                ],
            ],
            'n2' => [
                [
                    'cid' => 1,
                    'pid' => 1,
                    'c1' => 'p1col1-1',
                    'c2' => 'p1col2-1',
                ],
                [
                    'cid' => 2,
                    'pid' => 1,
                    'c1' => 'p1col1-2',
                    'c2' => 'p1col2-2',
                ],
                [
                    'cid' => 3,
                    'pid' => 3,
                    'c1' => 'p3col1-1',
                    'c2' => 'p3col2-1',
                ],
                [
                    'cid' => 4,
                    'pid' => 3,
                    'c1' => 'p3col1-2',
                    'c2' => 'p3col2-2',
                ],
                [
                    'cid' => 5,
                    'pid' => 3,
                    'c1' => 'p3col1-3',
                    'c2' => 'p3col2-3',
                ],
            ],
        ];
    }
}
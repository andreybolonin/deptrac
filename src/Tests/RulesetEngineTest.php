<?php


namespace DependencyTracker\Tests;


use DependencyTracker\ClassNameLayerResolver;
use DependencyTracker\Configuration;
use DependencyTracker\Configuration\ConfigurationRuleset;
use DependencyTracker\DependencyResult;
use DependencyTracker\DependencyResult\Dependency;
use DependencyTracker\RulesetEngine;
use Prophecy\Argument;
use SensioLabs\AstRunner\AstMap;

class RulesetEngineTest extends \PHPUnit_Framework_TestCase
{

    private function createDependencies(array $fromTo)
    {
        foreach ($fromTo as $from => $to) {
            yield new Dependency($from, 0, $to);
        }
    }

    public function dependencyProvider()
    {
        yield [
            [
                // ClassA has a Dependency on ClassB
                'ClassA' => 'ClassB'
            ],
            [
                // ClassA is in LayerA, ClassB is in LayerB
                'ClassA' => ['LayerA'],
                'ClassB' => ['LayerB']
            ],
            [
                'LayerA' => [
                    'LayerB'
                ],
                'LayerC' => []
            ],
            0

        ];

        yield [
            [
                'ClassA' => 'ClassB'
            ],
            [
                'ClassA' => ['LayerA'],
                'ClassB' => ['LayerB']
            ],
            [
                'LayerA' => [],
                'LayerB' => []
            ],
            1

        ];

        yield [
            [
                'ClassA' => 'ClassB'
            ],
            [
                'ClassA' => ['LayerA'],
                'ClassB' => ['LayerB']
            ],
            [],
            1

        ];

        yield [
            [
                'ClassA' => 'ClassB'
            ],
            [
                'ClassA' => [],
                'ClassB' => []
            ],
            [],
            0
        ];

        yield [
            [
                'ClassA' => 'ClassB'
            ],
            [
                'ClassA' => ['LayerA'],
                'ClassB' => ['LayerB']
            ],
            [
                'LayerA' => ['LayerB']
            ],
            0
        ];

        yield [
            [
                'ClassA' => 'ClassB'
            ],
            [
                'ClassA' => ['LayerA'],
                'ClassB' => ['LayerB']
            ],
            [
                'LayerB' => ['LayerA']
            ],
            1
        ];

        yield [
            [
                'ClassA' => 'ClassB',
                'ClassB' => 'ClassA',
                'ClassC' => 'ClassD'
            ],
            [
                'ClassA' => ['LayerA'],
                'ClassB' => ['LayerB'],
                'ClassC' => ['LayerC'],
                'ClassD' => ['LayerD']
            ],
            [],
            3
        ];
    }

    /**
     * @param array $dependenciesAsArray
     * @param array $classesInLayers
     * @param array $rulesetConfiguration
     * @param $expectedCount
     * @dataProvider dependencyProvider
     */
    public function testGetViolationsButNoViolations(array $dependenciesAsArray, array $classesInLayers, array $rulesetConfiguration, $expectedCount)
    {
        $dependencyResult = (new DependencyResult());
        foreach ($this->createDependencies($dependenciesAsArray) as $dep) {
            $dependencyResult->addDependency($dep);
        }

        $classNameLayerResolver = $this->prophesize(ClassNameLayerResolver::class);
        foreach ($classesInLayers as $classInLayer => $layers) {
            $classNameLayerResolver->getLayersByClassName($classInLayer)->willReturn($layers);
        }

        $this->assertCount(
            $expectedCount,
            (new RulesetEngine())->getViolations(
                $dependencyResult,
                $classNameLayerResolver->reveal(),
                ConfigurationRuleset::fromArray($rulesetConfiguration)
            )
        );
    }

}
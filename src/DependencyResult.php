<?php

namespace DependencyTracker;

use DependencyTracker\DependencyResult\Dependency;

class DependencyResult
{

    private $classLayerMap = [];

    private $dependencies = [];

    private $inheritDependencies = [];

    public function addDependency(Dependency $dependency)
    {
        if (!isset($this->dependencies[$dependency->getClassA()])) {
            $this->dependencies[$dependency->getClassA()] = [];
        }

        $this->dependencies[$dependency->getClassA()][] = $dependency;
    }

    public function addInheritDependency(Dependency $dependency)
    {
        if (!isset($this->inheritDependencies[$dependency->getClassA()])) {
            $this->inheritDependencies[$dependency->getClassA()] = [];
        }

        $this->inheritDependencies[$dependency->getClassA()][] = $dependency;
    }

    /**
     * @param $klass
     * @return Dependency[]
     */
    public function getDependenciesByClass($klass)
    {
        if (!isset($this->dependencies[$klass])) {
            return [];
        }

        return $this->dependencies[$klass];
    }

    /** @return Dependency[] */
    public function getDependencies()
    {
        $buffer = [];

        foreach (array_merge($this->dependencies, $this->inheritDependencies) as $deps) {
            foreach($deps as $dependency) {
                $buffer[] = $dependency;
            }
        }

        return $buffer;
    }

    public function addClassToLayer($klass, $layer)
    {
        if (!isset($this->classLayerMap[$klass])) {
            $this->classLayerMap[$klass] = [];
        }

        $this->classLayerMap[$klass][] = $layer;
    }

    public function getClassLayerMap()
    {
        return $this->classLayerMap;
    }

    public function getLayersByClassName($className)
    {
        if (!isset($this->classLayerMap[$className])) {
            return [];
        }

        return $this->classLayerMap[$className];
    }

}
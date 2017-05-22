<?php

namespace Zaggoware\Reflection;

use Zaggoware\Generic\KeyValuePair;
use Zaggoware\IDisposable;

class ClassWriter {
    const INDENTATION_SIZE = "    ";

    /**
     * @param TypeBuilder $builder
     */
    public function __construct(TypeBuilder $builder) {
        $this->builder = $builder;
    }

    /**
     * @var TypeBuilder
     */
    private $builder;

    /**
     * @return string
     */
    public function write() {
        return $this->writeStart() .
            $this->writeNamespace() .
            $this->writeReferencedTypes() .
            $this->writeClassDefinition() .
            $this->writeConstructor() .
            $this->writeFields() .
            $this->writeProperties() .
            $this->writeMethods() .
            $this->writeEnd();
    }

    /**
     * @param string $fileName
     */
    public function writeToFile($fileName) {
        $body = $this->write();
        $file = fopen($fileName, "w+");
        fwrite($file, $body);
        fclose($file);
    }

    protected function writeStart() {
        return "<?php". $this->eol(2);
    }

    protected function writeNamespace() {
        if ($this->builder->getNamespace() !== "\\") {
            return "namespace " . $this->builder->getNamespace() . ";". $this->eol(2);
        }
    }

    protected function writeReferencedTypes() {
        $types = $this->builder->getReferencedTypes()->orderBy(function(Type $type) { return $type->getName(); });

        if (!$types->any()) {
            return "";
        }

        $result = "";

        foreach ($types as $type) {
            /** @var Type $type */
            $result .= "use ". $type->getName() .";". $this->eol();
        }

        return $result . $this->eol();
    }

    protected function writeClassDefinition() {
        return "class ". $this->builder->getName() ." {". $this->eol();
    }

    protected function writeConstructor() {
        $constructor = $this->builder->getConstructor();

        if ($constructor === null) {
            return "";
        }

        return $this->writeMethod($constructor);
    }

    protected function writeFields() {
        $fields = $this->builder->getFields();

        if (!$fields->any()) {
            return "";
        }

        $result = $this->eol();

        foreach ($fields as $pair) {
            /** @var KeyValuePair $pair */
            /** @var FieldBuilder $field */
            $field = $pair->getValue();

            var_dump($field);

            $result .= $this->writeField($field);
        }

        return $result;
    }

    protected function writeField(FieldBuilder $fieldBuilder) {
        $result = "";

        if ($fieldBuilder->getType() !== null) {
            $leadingSlash = $fieldBuilder->getType()->getNamespace() === "\\" ? "\\" : "";
            $result .= $this->eol() . $this->indent() . "/** @var ". $leadingSlash . $fieldBuilder->getType()->getShortName() ." \$". $fieldBuilder->getName() ." */";
        }

        $result .= $this->eol() . $this->indent() . $fieldBuilder->getAccessModifier() . " \$". $fieldBuilder->getName();

        if ($fieldBuilder->hasDefaultValue()) {
            $result .= " = ". $fieldBuilder->getDefaultValue();
        }

        return $result .";". $this->eol();
    }

    protected function writeProperties() {
        $properties = $this->builder->getProperties();

        if (!$properties->any()) {
            return "";
        }

        $result = "";

        foreach ($properties as $pair) {
            /** @var KeyValuePair $pair */
            /** @var PropertyBuilder $property */
            $property = $pair->getValue();

            $result .= $this->writeField($property->getField());
        }

        foreach ($properties as $pair) {
            /** @var KeyValuePair $pair */
            /** @var PropertyBuilder $property */
            $property = $pair->getValue();

            if ($property->hasGetterMethod()) {
                $result .= $this->eol() . $this->writeMethod($property->getGetterMethod()) . $this->eol();
            }

            if ($property->hasSetterMethod()) {
                $result .= $this->eol() . $this->writeMethod($property->getSetterMethod()) . $this->eol();
            }
        }

        return $result;
    }

    protected function writeMethods() {
        $methods = $this->builder->getMethods();

        if (!$methods->any()) {
            return "";
        }

        $result = "";

        foreach ($methods as $pair) {
            /** @var KeyValuePair $pair */
            /** @var MethodBuilder $property */
            $method = $pair->getValue();

            $result .= $this->eol() . $this->writeMethod($method) . $this->eol();
        }

        return $result;
    }

    protected function writeMethod(MethodBuilder $methodBuilder) {
        $name = $methodBuilder->getName();

        if ($name === $this->builder->getName()) {
            $name = "__construct";
        }

        $result = "function $name(";

        if (!$this->builder->isInterface() && $methodBuilder->getAccessModifier() == AccessModifier::PUBLIC_M) {
            $result = AccessModifier::PUBLIC_M ." ". $result;
        }

        $result = $this->indent() . $result;
        $params = $methodBuilder->getParameters();
        $paramsCount = $params->count();

        if ($methodBuilder->getType() !== null || $paramsCount > 0) {
            $docComment = $this->indent() . "/**". $this->eol();

            foreach ($params as $param) {
                /** @var MethodParameterBuilder $param */

                $docComment .=  $this->indent() ." * @param ";

                if ($param->isStronglyTyped() && $param->getType() !== null) {
                    $leadingSlash = $param->getType()->getNamespace() === "\\" ? "\\" : "";
                    $docComment .= $leadingSlash . $param->getType()->getShortName() ." ";
                }

                $docComment .= "\$". $param->getName() . $this->eol();
            }

            if ($methodBuilder->getType() !== null && !$methodBuilder->getType()->equals(Type::void())) {
                $leadingSlash = $methodBuilder->getType()->getNamespace() === "\\" ? "\\" : "";
                $docComment .= $this->indent() ." * @return ". $leadingSlash . $methodBuilder->getType()->getShortName() . $this->eol();
            }

            $docComment .=  $this->indent() ."*/". $this->eol();
            $result = $docComment . $result;
        }

        $counter = 1;
        foreach ($params as $param) {
            /** @var MethodParameterBuilder $param */

            if ($param->isStronglyTyped()) {
                $leadingSlash = $param->getType()->getNamespace() === "\\" ? "\\" : "";
                $result .= $leadingSlash . $param->getType()->getShortName() ." ";
            }

            $result .= "\${$param->getName()}";

            if ($param->hasDefaultValue()) {
                $result .= " = {$param->getDefaultValue()}";
            }

            if ($counter < $paramsCount) {
                $result .= ", ";
            }

            $counter++;
        }

        $result .= ")";

        if ($this->builder->isInterface()) {
            $result .= ";";
        } else {
            $result .= " {". $this->eol() . $this->indent(2) . $methodBuilder->getBody() . $this->eol() . $this->indent() ."}";
        }

        return $result;
    }

    protected function writeEnd(){
        return $this->eol() ."}";
    }

    private function indent($size = 1) {
        $result = "";

        for ($i=0; $i<$size; $i++) {
            $result .= self::INDENTATION_SIZE;
        }

        return $result;
    }

    private function eol($size = 1) {
        $result = "";

        for ($i=0; $i<$size; $i++) {
            $result .= PHP_EOL;
        }

        return $result;
    }
} 
<?php

namespace Zaggoware\Reflection;

use Zaggoware\Exceptions\ArgumentNullException;
use Zaggoware\Exceptions\InvalidArgumentTypeException;
use Zaggoware\Generic\ArrayList;
use Zaggoware\Generic\Dictionary;
use Zaggoware\Generic\IEnumerable;

class MethodBuilder implements IReflectBuilder {
    const CONSTRUCTOR = "__construct";

    /**
     * @param string $name
     */
    public function __construct($name = null) {
        $this->name = $name;
        $this->accessModifier = AccessModifier::METHOD_DEFAULT;
        $this->returnType = Type::void();
        $this->parameters = new Dictionary();
    }

    /** @var string */
    private $name;

    /** @var string */
    private $accessModifier;

    /** @var Type */
    private $returnType;

    /** @var Dictionary<string, MethodParameterBuilder> */
    private $parameters;

    /** @var string */
    private $body;

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Type
     */
    public function getType() {
        return $this->returnType;
    }

    /**
     * @param Type $type
     * @return $this
     */
    public function setType(Type $type) {
        $this->returnType = $type;

        return $this;
    }

    /**
     * @return IEnumerable
     */
    public function getParameters() {
        return new ArrayList($this->parameters->getValues());
    }

    /**
     * @param IEnumerable|\IteratorAggregate|array|MethodParamBuilder... $parameters
     * @param MethodParamBuilder...|null $_
     * @throws ArgumentNullException
     * @throws \Zaggoware\Exceptions\DuplicateKeyException
     * @return $this
     */
    public function setParameters($parameters, $_ = null) {
        if ($parameters === null) {
            throw new ArgumentNullException("parameters");
        }

        $funcArgs = func_get_args();

        if (count($funcArgs) > 1) {
            $parameters = $funcArgs;
        } else if ($parameters instanceof MethodParameterBuilder) {
            $parameters = array($parameters);
        }

        $this->parameters->clear();

        foreach ($parameters as $param) {
            if (!($param instanceof MethodParameterBuilder)) {
                throw new InvalidArgumentTypeException("parameter", $param, new Type("MethodParameterBuilder"));
            }

            $this->parameters->add($param->getName(), $param);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body) {
        // TODO: It would be better if we could parse a callable (anonymous function), but unfortunately we cannot do that :(
        $this->body = $body;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccessModifier() {
        return $this->accessModifier;
    }

    /**
     * @param string $accessModifier See AccessModifier.
     * @return $this
     */
    public function setAccessModifier($accessModifier) {
        if (!AccessModifier::validate($accessModifier)) {
            throw new \InvalidArgumentException("'$accessModifier' is not a valid access modifier.");
        }

        $this->accessModifier = $accessModifier;

        return $this;
    }
}
<?php

namespace JSomerstone\DaysWithoutBundle\Lib;


class InputValidator
{
    private $validationRules = array(
        'nick' => array(
            'pattern' => '[a-zA-Z]{3,48}',
            'regexp' => '/^[a-zA-Z]{3,48}$/',
            'message' => 'Nick must have 3-48 characters between A-z'
        ),
        'password' => array(
            'pattern' => '.{8,128}',
            'regexp' => '/.{8,128}/',
            'message' => 'Password must be at least 8 characters long'
        ),
    );

    /**
     * @return array
     */
    public function getValidationRules()
    {
        return $this->validationRules;
    }

    /**
     * @param $string
     * @throws InputValidatorException if field is invalid
     */
    public function validateNick($string)
    {
        return $this->validateField('nick', $string);
    }

    /**
     * @param string $fieldName
     * @param string $string
     * @throws InputValidatorException
     */
    public function validateField($fieldName, $string)
    {
        if (true !== self::validateString($this->getRegexpForField($fieldName), $string))
        {
            throw new InputValidatorException(
                $fieldName,
                $this->getMessageForField($fieldName)
            );
        }
    }

    private static function validateString($pattern, $string)
    {
        return (bool)preg_match(
            $pattern,
            $string
        );
    }

    /**
     * @param string $fieldName
     * @return string regular expression
     * @throws InputValidatorException
     */
    public function getRegexpForField($fieldName)
    {
        if ( ! isset($this->validationRules[$fieldName])
            ||  ! isset($this->validationRules[$fieldName]['regexp']))
        {
            throw new InputValidatorException(
                $fieldName,
                "Missing pattern for field '$fieldName'"
            );
        }
        return $this->validationRules[$fieldName]['regexp'];
    }

    /**
     * @param string $fieldName
     * @return string regular expression
     * @throws InputValidatorException
     */
    protected function getMessageForField($fieldName)
    {
        if ( ! isset($this->validationRules[$fieldName])
            ||  ! isset($this->validationRules[$fieldName]['message']))
        {
            throw new InputValidatorException(
                $fieldName,
                "Missing message for field '$fieldName'"
            );
        }
        return $this->validationRules[$fieldName]['message'];
    }

    /**
     * @param string $password
     * @param string $confirmation same password again
     * @throws InputValidatorException
     */
    public function validatePassword($password, $confirmation)
    {
        if ( $password !== $confirmation)
        {
            throw new InputValidatorException('password', 'Passwords do not match');
        }
        return $this->validateField('password', $password);
    }
}

class InputValidatorException extends \JSomerstone\DaysWithoutBundle\Exception\PublicException
{
    private $invalidField;

    public function __construct($fieldName, $message = null)
    {
        $this->invalidField = $fieldName;
        $message = is_null($message)
            ? "Field '$fieldName' is invalid"
            : $message;
        parent::__construct($message);
    }
}

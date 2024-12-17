<?php

/**
 * Class OptionsData
 *
 * Singleton class for managing options with getters and setters.
 * Provides a way to store and retrieve values for specific options.
 * Ensures that once a property is set, it cannot be changed again.
 */
class OptionsData
{
    /**
     * @var OptionsData|null $instance The single instance of the OptionsData class.
     */
    public static ?OptionsData $instance = null;

    /**
     * @var int|null $testOption An example of a test option.
     */
    public ?int $testOption = null;

    /**
     * Private constructor to prevent direct instantiation.
     * Initializes the class as a singleton.
     */
    private function __construct()
    {
    }

    /**
     * Returns the single instance of the OptionsData class.
     *
     * @return OptionsData The singleton instance.
     */
    public static function getInstance(): OptionsData
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Magic setter to assign values to class properties.
     * Throws an exception if the property has already been set or if the property is invalid.
     *
     * @param string $name  The name of the property to set.
     * @param mixed  $value The value to assign to the property.
     *
     * @throws \Exception If the property is already set or if the property name is invalid.
     */
    public function __set(string $name, $value): void
    {
        if (property_exists($this, $name)) {
            if ($this->$name === null) {
                $this->$name = $value;
            } else {
                throw new \Exception("$name has already been set and cannot be changed.");
            }
        } else {
            throw new \Exception("Invalid property name: $name");
        }
    }

    /**
     * Magic getter to retrieve the value of a class property.
     * Throws an exception if the property does not exist.
     *
     * @param string $name The name of the property to get.
     *
     * @return mixed The value of the property.
     *
     * @throws \Exception If the property does not exist.
     */
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new \Exception("Invalid property name: $name");
    }

    /**
     * Magic isset method to check if a property is set and not null or empty.
     *
     * @param string $name The name of the property to check.
     *
     * @return bool True if the property is set and not null or empty, false otherwise.
     */
    public function __isset(string $name): bool
    {
        if (property_exists($this, $name) && $this->$name !== null && $this->$name !== false && $this->$name !== '') {
            return true;
        } else {
            return false;
        }
    }
}

<?php

/**
 * @file
 * Base abstract class for AppleNews classes.
 */

namespace ChapterThree\AppleNews;

/**
 * Base abstract class for AppleNews classes.
 */
abstract class PushAPI_Abstract {

  /**
   * Make Authentication method requried.
   */
  abstract protected function Authentication($method, $path, Array $arguments);

  /**
   * Make RequestData method requried.
   */
  abstract public function RequestData($method, $path, Array $arguments);

  /**
   * Make Path method requried.
   */
  abstract protected function Path($path, Array $arguments);

  /**
   * Make Request method requried.
   */
  abstract public function Request($method, $path, Array $arguments);

  /**
   * Make Response method required.
   */
  abstract protected function Response($data);

  /**
   * Implements __get().
   */
  public function __get($name) {
    return $this->$name;
  }

  /**
   * Implements __set().
   *
   * Intended to be overridden by subclass.
   */
  public function __set($name, $value) {
    $this->triggerError('Undefined property via __set(): ' . $name);
    return NULL;
  }

  /**
   * Implements __isset().
   */
  public function __isset($name) {
    return isset($this->$name);
  }

  /**
   * Implements __unset().
   */
  public function __unset($name) {
    unset($this->$name);
  }

  /**
   * Error handler.
   */
  public function triggerError($message, $message_type = E_USER_NOTICE) {
    $trace = debug_backtrace();
    trigger_error($message . ' in ' . $trace[0]['file'] . ' on line ' .
      $trace[0]['line'], $message_type);
  }

}

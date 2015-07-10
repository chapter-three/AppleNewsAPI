<?php

/**
 * @file
 * Base abstract class for AppleNews classes.
 */

namespace ChapterThree\AppleNews\PushAPI;

/**
 * Base abstract class for AppleNews classes.
 */
abstract class PushAPI {

  /**
   * Make HHMAC method requried.
   */
  abstract protected function HHMAC($cannonical_request);

  /**
   * Make Authentication method requried.
   */
  abstract protected function Authentication();

  /**
   * Make Path method requried.
   */
  abstract protected function Path();

  /**
   * Make PreprocessData method required.
   */
  abstract protected function PreprocessData($method, $path, Array $path_args, Array $vars);

  /**
   * Make SetHeaders method required.
   */
  abstract protected function SetHeaders(Array $headers);

  /**
   * Make UnsetHeaders method required.
   */
  abstract protected function UnsetHeaders(Array $headers);

  /**
   * Make SetOption method required.
   */
  abstract protected function SetOption($name, $value);

  /**
   * Make Request method required.
   */
  abstract protected function Request($data);

  /**
   * Make Response method required.
   */
  abstract protected function Response($response);

  /**
   * Make Get method requried.
   */
  abstract public function Get($path, Array $path_args, Array $data);

  /**
   * Make Post method requried.
   */
  abstract public function Post($path, Array $path_args, Array $data);

  /**
   * Make Delete method requried.
   */
  abstract public function Delete($path, Array $path_args, Array $data);

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

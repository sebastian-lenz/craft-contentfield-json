<?php

namespace lenz\contentfield\json\scope;

/**
 * Class DefinitionWriter
 */
class DefinitionWriter
{
  /**
   * @var array
   */
  public $lines = [];

  /**
   * @var int
   */
  public $stack = [];


  /**
   * @return string
   */
  public function __toString(): string {
    return implode("\n", $this->lines);
  }

  /**
   * @param string $code
   * @return $this
   */
  public function beginScope(string $type, string $code): DefinitionWriter {
    $this->push("$code {");
    $this->stack[] = $type;
    return $this;
  }

  /**
   * @param string $name
   * @param string $extends
   * @return $this
   */
  public function beginInterfaceScope(string $name, string $extends = ''): DefinitionWriter {
    $declaration = $this->inNamespace() ? '' : 'declare ';
    $declaration .= "interface $name ";
    if ($extends) {
      $declaration .= "extends $extends ";
    }

    return $this->beginScope('interface', $declaration);
  }

  /**
   * @param string $name
   * @param string $extends
   * @return $this
   */
  public function beginNamespaceScope(string $name): DefinitionWriter {
    if (!empty($name)) {
      return $this->beginScope('namespace', "declare namespace $name ");
    }

    return $this;
  }

  /**
   * @param array $lines
   * @return $this
   */
  public function docComment(array $lines): DefinitionWriter {
    $this->push('/**');
    foreach ($lines as $line) {
      $this->push(" * $line");
    }
    $this->push(' */');

    return $this;
  }

  /**
   * @return $this
   */
  public function endScope(): DefinitionWriter {
    if (count($this->stack) > 0) {
      array_pop($this->stack);
      $this->push('}');
      $this->push('');
    }

    return $this;
  }

  /**
   * @return bool
   */
  public function inNamespace(): bool {
    foreach ($this->stack as $entry) {
      if ($entry == 'namespace') {
        return true;
      }
    }

    return false;
  }

  /**
   * @param string $value
   * @return $this
   */
  public function push(string $value): DefinitionWriter {
    $indent = str_repeat('  ', count($this->stack));

    if (strpos($value, "\n") === false) {
      $this->lines[] = $indent . $value;
    } else {
      foreach (explode("\n", $value) as $line) {
        $this->lines[] = $indent . $line;
      }
    }

    return $this;
  }

  /**
   * @param string $filename
   */
  public function pushFile(string $filename) {
    $this->lines[] = file_get_contents($filename);
  }
}

<?php
namespace Coroq\Controller;

class ViewRenderer {
  /** @var string */
  protected $template_directory;

  public function __construct(string $template_directory) {
    $this->template_directory = $template_directory;
  }

  public function render(string $__template_name, array $__arguments = []): string {
    try {
      ob_start();
      extract($__arguments);
      include "$this->template_directory/$__template_name";
      return ob_get_clean();
    }
    catch (\Throwable $error) {
      ob_end_clean();
      throw $error;
    }
  }

  public function __invoke(string $template_name, array $arguments = []): string {
    return $this->render($template_name, $arguments);
  }
}

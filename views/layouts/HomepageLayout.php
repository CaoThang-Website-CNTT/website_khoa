<?php

namespace App\Views\Layouts;

use App\Types\IBaseViewComponent;
use App\Views\Components\{Header, Footer, WhyChooseUs};

class HomepageLayout implements IBaseViewComponent
{
  private string $content;
  public function __construct(string $content = 'Khoa CNTT - Cao Thắng')
  {
    $this->content = $content;
  }
  public function render(): string
  {
    $Header = new Header();
    $Footer = new Footer();
    $WhyChooseUs = new WhyChooseUs();

    ob_start();
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="icon" type="image/png" sizes="32x32" href="./favicon-32x32.png">
      <link rel="preload" as="style" href="../assets/css/fonts.css">
      <link rel="stylesheet" href="../assets/css/fonts.css">
      <link rel="preload" as="style" href="../assets/css/base.css">
      <link rel="stylesheet" href="../assets/css/base.css">

      <title>Khoa Công nghệ Thông tin - Trường CĐKT Cao Thắng</title>
    </head>

    <body style="width: 1440px; padding: 0 152px 0 152px; margin: 0 auto;">
      <?= $Header->render() ?>
      <?= $this->content ?>
      <?= $WhyChooseUs->render() ?>
      <?= $Footer->render() ?>
    </body>

    </html>
<?php

    return ob_get_clean();
  }
}

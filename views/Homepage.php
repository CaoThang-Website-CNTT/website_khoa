<?php

namespace App\Views;

use App\Types\IBaseViewComponent;
use App\Views\Components\WhyChooseUs;
use App\Views\Layouts\HomepageLayout;

class Homepage implements IBaseViewComponent
{
  public function render(): string
  {
    $Content = "Hello mng, đây là Khoa CNTT - Trường CĐKT Cao Thắng";

    $Layout = new HomepageLayout($Content);

    ob_start();
?>
    <?= $Layout->render() ?>
<?php

    return ob_get_clean();
  }
}

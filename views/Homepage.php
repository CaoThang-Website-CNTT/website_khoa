<?php

namespace App\Views;

use App\Core\ViewComponent;
use App\Views\Layouts\HomepageLayout;

class Homepage extends ViewComponent
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

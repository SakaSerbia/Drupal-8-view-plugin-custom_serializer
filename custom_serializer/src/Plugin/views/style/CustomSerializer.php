<?php

namespace Drupal\custom_serialization\Plugin\views\style;

use Drupal\rest\Plugin\views\style\Serializer;

/**
* The style plugin for serialized output formats.
*
* @ingroup views_style_plugins
*
* @ViewsStyle(
*   id = "custom_serializer",
*   title = @Translation("Custom serializer"),
*   help = @Translation("Serializes views row data using the Serializer
*   component."), display_types = {"data"}
* )
*/
class CustomSerializer extends Serializer {

    /**
    * {@inheritdoc}
    */
    public function render() {
        $rows = [];
        // If the Data Entity row plugin is used, this will be an array of entities
        // which will pass through Serializer to one of the registered Normalizers,
        // which will transform it to arrays/scalars. If the Data field row plugin
        // is used, $rows will not contain objects and will pass directly to the
        // Encoder. number_format((float)$iot_float, 2, '.', '') bcadd($iot_float, 0, 2);
        // $iot_date = (int) $row_render["created"]->__toString();
        foreach ($this->view->result as $row_index => $row) {
                $this->view->row_index = $row_index;
                $row_render = $this->view->rowPlugin->render($row);
                $iot_float = 0;
                $iot_date = 0;
                if (isset($row_render["field_iot_float"]) && $row_render["field_iot_float"] instanceof \Drupal\Core\Render\Markup){
                    $iot_float = (float) $row_render["field_iot_float"]->__toString();
                    $iot_date = (int) $row_render["created"]->__toString();
            }
            $rows[] = [
                $iot_date*1000,
                $iot_float
            ];
        }
        unset($this->view->row_index);

        // Get the content type configured in the display or fallback to the
        // default.
        if ((empty($this->view->live_preview))) {
            $content_type = $this->displayHandler->getContentType();
        }
        else {
            $content_type = !empty($this->options['formats']) ? reset($this->options['formats']) : 'json';
        }
        return $this->serializer->serialize($rows, $content_type, ['views_style_plugin' => $this]);
    }
}
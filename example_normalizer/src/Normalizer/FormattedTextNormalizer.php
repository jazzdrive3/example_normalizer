<?php

namespace Drupal\example_normalizer\Normalizer;

use Drupal\jsonapi\Normalizer\FieldItemNormalizer;
use Drupal\text\Plugin\Field\FieldType\TextItemBase;
use Drupal\jsonapi\Normalizer\Value\FieldItemNormalizerValue;
use Drupal\filter\Entity\FilterFormat;
use Drupal\filter\Plugin\Filter\FilterHtml;

class FormattedTextNormalizer extends FieldItemNormalizer {

  protected $supportedInterfaceOrClass = TextItemBase::class;

  public function normalize($field_item, $format = NULL, array $context = [])
  {
    /** @var \Drupal\Core\TypedData\TypedDataInterface $property */
    $values = [];
    // We normalize each individual property, so each can do their own casting,
    // if needed.
    foreach ($field_item as $property_name => $property) {
      if ($property_name == 'value') {
        $value = $property->getValue();
        $format = FilterFormat::load($field_item->format);

        $filter = $format->filters()->get('filter_html');
        $property = $filter->process($value, 'en')->getProcessedText();
      }

      $values[$property_name] = $this->serializer->normalize($property, $format, $context);
    }

    if (isset($context['langcode'])) {
      $values['lang'] = $context['langcode'];
    }
    return new FieldItemNormalizerValue($values);
  }
}

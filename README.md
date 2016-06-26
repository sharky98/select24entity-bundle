select24entity-bundle
====================

## Fork description

This bundle is a fork of the original [Select2Entity by Tetranz](https://github.com/tetranz/select2entity-bundle).

The goal of this fork is to modify the bundle to mimick Select2 behavior, meaning leaving the activation and the configuration of the field to the user in the javascript itself, at the exception of AJAX retrieval of the entities.

## Introduction

This is a Symfony2 bundle which enables the popular [Select2](https://select2.github.io) component to be used as a drop-in replacement for a standard entity field on a Symfony form.

The main feature that this bundle provides compared with the standard Symfony entity field (rendered with a html select) is that the list is retrieved via a remote ajax call. This means that the list can be of almost unlimited size. The only limitation is the performance of the database query or whatever that retrieves the data in the remote web service.

It works with both single and multiple selections. If the form is editing a Symfony entity then these modes correspond with many to one and many to many relationships. In multiple mode, most people find the Select2 user interface easier to use than a standard select tag with multiple=true with involves awkward use of the ctrl key etc.

The project was inspired by [lifo/typeahead-bundle](https://github.com/lifo101/typeahead-bundle) which uses the Typeahead component in Bootstrap 2 to provide similar functionality. Select24Entity can be used anywhere Select2 can be installed, including Bootstrap 3.

Thanks to @ismailbaskin we now have Select2 version 4 compatibility.

## Screenshots

This is a form with a single selection field list expanded.

![Single select example](Resources/doc/img/single.png)

This is a form with a multiple selection field list expanded.

![Multiple select example](Resources/doc/img/multi.png)

## Installation

Select2 must be installed and working first.

 * select2.js, select2.css from https://github.com/select2/select2

 * If you want it integrated in bootstrap, select2-bootstrap.css from https://github.com/select2/select2-bootstrap-theme

These files live in the Resources/public/js and Resources/public/css folders of one of my bundles and then included in my main layout.html.twig file.

Alternatively, minified versions of select2.js and select2.css can be loaded from the CloudFlare CDN using the two lines of code given here: [https://select2.github.io](https://select2.github.io). Make sure the script tag comes after where jQuery is loaded. That might be in the page footer.

* Add `brunops/select24entity-bundle` to your projects `composer.json` "requires" section:

```javascript
{
  // ...
  "require": {
    // ...
    "brunops/select24entity-bundle": "dev-master"
  }
}
```
Note that this only works with Select2 version 4.

* Run `php composer.phar update brunops/select24entity-bundle` in your project root.
* Update your project `app/AppKernel.php` file and add this bundle to the $bundles array:

```php
$bundles = array(
  // ...
  new Brunops\Select24EntityBundle\BrunopsSelect24EntityBundle(),
);
```

* Update your project `app/config.yml` file to provide global twig form templates:

```yaml
twig:
  form_themes:
    - 'BrunopsSelect24EntityBundle:Form:fields.html.twig'

```

* Load the Javascript on the page. The simplest way is to add the following to your layout file. Don't forget to run console assets:install. Alternatively, do something more sophisticated with Assetic.

```html
<script src="{{ asset('bundles/brunopsselect24entity/js/select24entity.js') }}"></script>
```

## How to use

The following works on Symfony 2.8 (and probably Symfony 3, but not tested yet).

Select24Entity is simple to use. In the buildForm method of a form type class, specify `Select24EntityType::class` as the type where you would otherwise use `entity:class`.

Here's an example:
```php
$builder
  ->add('country', Select24EntityType::class, [
    'multiple' => true,
    'remote_route' => 'brunops_test_default_countryquery',
    'class' => '\Brunops\TestBundle\Entity\Country',
    'primary_key' => 'id',
    'text_property' => 'name',
    'minimum_input_length' => 2,
    'page_limit' => 10,
    'allow_clear' => true,
    'delay' => 250,
    'cache' => true,
    'cache_timeout' => 60000, // if 'cache' is true
    'language' => 'en',
    'placeholder' => 'Select a country',
  ])
```

Put this at the top of the file with the form type class:
```php
use Brunops\Select24EntityBundle\Form\Type\Select24EntityType;
```

In the template where you will use the field, you must activate it like you would with any Select2 field, using something like this:
```javascript
$('.select24entity').select24entity();
```

Inside the parenthesis, you can pass any options that [Select2](https://select2.github.io/) can accept.

## Options
Defaults will be used for some if not set.
* `class` is your entity class. Required
* `primary_key` is the name of the property used to uniquely identify entities. Defaults to 'id'
* `text_property` This is the entity property used to retrieve the text for existing data.
If text_property is omitted then the entity is cast to a string. This requires it to have a __toString() method.
* `multiple` True for multiple select (many to many). False for single (many to one) select.
* `minimum_input_length` is the number of keys you need to hit before the search will happen. Defaults to 2.
* `page_limit` This is passed as a query parameter to the remote call. It is intended to be used to limit size of the list returned. Defaults to 10.
* `allow_clear` True will cause Select2 to display a small x for clearing the value. Defaults to false.
* `delay` The delay in milliseconds after a keystroke before trigging another AJAX request. Defaults to 250 ms.
* `placeholder` Placeholder text.
* `language` i18n language code. Defaults to en.
* `cache` Enable AJAX cache. Results will be cached for each 'term' queried.
* `cache_timeout` How long to cache a query in milliseconds. Setting to `0` will cause the cache to never timeout _(60000 = 60 seconds)_
* `transformer` The fully qualified class name of a custom transformer if you need that flexibility as described below.

The url of the remote query can be given by either of two ways: `remote_route` is the Symfony route. `remote_params` can be optionally specified to provide parameters. Alternatively, `remote_path` can be used to specify the url directly.

The defaults can be changed in your app/config.yml file with the following format.

```yaml
brunops_select24entity:
  minimum_input_length: 2
  page_limit: 8
  allow_clear: true
  delay: 500
  language: fr
  cache: false
  cache_timeout: 0
```

## AJAX Response
The controller should return a `JSON` array in the following format. The properties must be `id` and `text`.

```javascript
[
  { id: 1, text: 'Displayed Text 1' },
  { id: 2, text: 'Displayed Text 2' }
]
```

## Select2 Taggable fields
If you want to use [Select2 Tags fields](https://select2.github.io/examples.html#tags), you need to do two things:

1. Activate the field with tags attribute to true:

```javascript
$(".select24entity").select24entity({
  tags: true
});
```

2. Define a [Data Transformer](http://symfony.com/doc/2.8/cookbook/form/data_transformers.html) that act similar to this:

```php
// Transform should return the same thing as the argument, Select24Entity will do the job.
public function transform($entities) {
  return $entities;
}

// This data transformer will receive the entities already parsed through the Select24Entity data transformer, alongside a key 'toCreate' if there is something to create.
public function reverseTransform($entities) {
  if ($entities->containsKey('toCreate')) {
    $toCreate = $entities->get('toCreate');
    $entities->remove('toCreate'); // We need to remove the key so that the Symfony Form component won't try to read it as if it was of same type of all other $entities
    // Simple loop to create the entities. Don't forget to persist them!
    foreach ($toCreate as $value) {
      if ($this->is_valid($value)) { // Some validation
        $newEntity = $this->createAndPersistEntity($value); // Create the entity object and persist it
        $entities->add($newEntity); // Add the entity to the ArrayCollection
      }
    }
  }
  return $entities;
}
```

##Custom option text##
If you need more flexibility in what you display as the text for each option, such as displaying the values of several fields from your entity or showing an image inside, you may define your own custom transformer.
Your transformer must implement DataTransformerInterface. The easiest way is probably to extend EntityToPropertyTransformer or EntitiesToPropertyTransformer and redefine the transform() method. This way you can return as `text` anything you want, not just one entity property.

Here's an example that returns the country name and continent (two different properties in the Country entity):
```php
$builder
  ->add('country', Select24EntityType::class, [
    'multiple' => true,
    'remote_route' => 'brunops_test_default_countryquery',
    'class' => '\Brunops\TestBundle\Entity\Country',
    'transformer' => '\Brunops\TestBundle\Form\DataTransformer\CountryEntitiesToPropertyTransformer',
  ]);
```
In transform sets data array like this:
```php
$data[] = array(
  'id' => $country->getId(),
  'text' => $country->getName().' ('.$country->getContinent()->getName().')',
);
```
Your custom transformer and respectively your Ajax controller should return an array in the following format:
```javascript
[
  { id: 1, text: 'United Kingdom (Europe)' },
  { id: 1, text: 'China (Asia)' }
]
```

###Templating###

If you need [Templating](https://select2.github.io/examples.html#templating) in Select2, you could consider the following example that shows the country flag next to each option.

Your custom transformer should return data like this:
```javascript
[
  { id: 1, text: 'United Kingdom (Europe)', img: 'images/flags/en.png' },
  { id: 2, text: 'China (Asia)', img: 'images/flags/ch.png' }
]
```
You need to define your own JavaScript function `select24entityAjax` which extends the original one `select24entity` and display custom template with image:
```javascript
$.fn.select24entityAjax = function(action) {
  var action = action || {};
  var template = function (item) {
    var img = item.img || null;
    if (!img) {
      if (item.element && item.element.dataset.img) {
        img = item.element.dataset.img;
      } else {
        return item.text;
      }
    }
    return $(
      '<span><img src="' + img + '" class="img-circle img-sm"> ' + item.text + '</span>'
    );
  };
  this.select24entity($.extend(action, {
    templateResult: template,
    templateSelection: template
  }));
  return this;
};
$('.select2entity').select24entityAjax();
```
This script will add the functionality globally for all elements with class `select24entity`, but if the `img` is not passed it will work as the original `select24entity`.

You also will need to override the following block in your template:
```twig
{% block brunops_select24entity_widget_select_option %}
  <option value="{{ label.id }}" selected="selected"
    {% for key, data in label %}
      {% if key not in ['id', 'text'] %} data-{{ key }}="{{ data }}"{% endif %}
    {% endfor %}>
    {{ label.text }}
  </option>
{% endblock %}
```
This block adds all additional data needed to the JavaScript function `select24entityAjax`, like data attribute. In this case we are passing `data-img`.

##Embed Collection Forms##
If you use [Embedded Collection Forms](http://symfony.com/doc/current/cookbook/form/form_collections.html) and [data-prototype](http://symfony.com/doc/current/cookbook/form/form_collections.html#allowing-new-tags-with-the-prototype) to add new elements in your form, you will need the following JavaScript that will listen for adding an element `.select24entity`:
```javascript
$('body').on('click', '[data-prototype]', function(e) {
  $(this).prev().find('.select24entity').last().select24entity();
});

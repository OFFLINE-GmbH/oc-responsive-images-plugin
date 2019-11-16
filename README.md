# ResponsiveImages Plugin for October CMS

Automatically generate and serve images for your visitor's viewport size without changing your theme!


## Features

* [Responsive images](#responsive-images)
* [Automatic WebP conversion](#automatic-webp-conversion)
* [Focuspoint](#focuspoint)
* [Inline SVG helper function](#inline-svg-helper-function)

## Responsive images

### How it works

This plugin provides a middleware that adds `srcset` and `sizes` attributes to all locally served images in your html
 response.

It turns this

```
<img width="500" src="/storage/app/media/image.jpg">
```

into this

```
<img width="500" src="/storage/app/media/image.jpg" srcset="/storage/temp/public/be7/4d6/0cc/image__400.jpg 400w, /storage/temp/public/be7/4d6/0cc/image__768.jpg 768w, /storage/temp/public/be7/4d6/0cc/image__1024.jpg 1024w" sizes="(max-width: 500px) 100vw, 500px">
```
 
It automatically creates resized copies of the image and serves the most fitting one to your visitor.

All image copies are saved in your public temp path. Remote file systems are currently untested.

The images are generated on the first page load. Depending on the source image size this may take a few seconds. 
Subsequent page loads will be faster since the images are only resized once.

### Configuration

Three image sizes are created by default: 400, 768 and 1024 pixels. 

You can change these values by changing the settings in the backend.

#### Alternative `src` and `srcset` attributes

If you want to use an alternative `src` attribute you can change this via the backend settings page.
 
This is useful if you are using a plugin like [jQuery.lazyLoad](http://www.appelsiini.net/projects/lazyload) where the image
 is initially linked via a `data-original` attribute.
 
 If your plugin requires an alternative srcset attribute (like [verlok/LazyLoad](https://github.com/verlok/lazyload)) this can also be specified via the backend settings. 


#### Global `class` attributes

If you want to add a class to every processed image you can configure this via the backend settings.

This is useful if you want to add Bootstrap's `img-responsive` class to all images on your website.

#### Pre-generate images

You can use the `php artisan responsive-images:generate` command to pre-generate responsive images. The command uses 
October's `pages.menuitem.*` events to build a list of all available URLs and pre-generates all images used on these 
pages. 

#### Test results

I have tested this plugin on a page with 20 hd wallpapers from pixabay.

| Viewport width | Transferred file size |
| -------------: | ---------------------:|
|        1920 px |               21.8 MB |
|        1024 px |                3.1 MB |
|         768 px |                2.0 MB |
|         400 px |                0.8 MB |

## Automatic WebP conversion

This plugin provides an option to automatically convert all images to the WebP image format
if the visiting Browser signals support for it.

Be aware that each WebP image is created on-demand with the first page view that requests it.
This might lead to slow page load times for your first visitors. To prevent this, warm up
the image cache by visiting every page at least once or
 use the `php artisan responsive-images:generate -v` console command.

To make use of this feature, enable it via October's backend settings. If you are using
Apache with `.htaccess` support, the plugin will whitelist the required `webp.php` helper
script for you automatically.

**If you do not use Apache**, please make sure that you whitelist requests to `plugins/offline/responsiveimages/webp.php`.

As soon as the WebP feature is enabled, all your images will be served by the [webp.php](./webp.php)
helper script. It converts requested images to the WebP format and serves it to the browser.

To serve the images with the least latency possible, the helper script is completely decoupled from
 October. It does not boot the whole Laravel framework for every request.
 
### Custom prefix 

With the WebP feature enabled, you might no longer like the way your image URLs look:

```
http://web.site/plugins/offline/responsiveimages/webp.php?path=/storage/temp/public/343/724/7f6/thumb_73_500_0_0_0_auto__500.png
```

If this bothers you, it is possible to specify a custom prefix for the WebP image paths via
October's backend settings. This will turn the URL above into something like this:

```
http://web.site/<custom-prefix>/storage/temp/public/343/724/7f6/thumb_73_500_0_0_0_auto__500.png
```

To make this new URL structure work, you have to create a server-side rewrite rule that
passes everything that comes after the custom prefix to the `webp.php` helper script as a `?path` GET
parameter:

```
FROM    /custom-prefix/storage/image/path
TO      /plugins/offline/responsiveimages/webp.php?path=/storage/image/path
```
 
Take a look at your web server's documentation on how to create such rewrites.

This is an example `RewriteRule` using Apache's `.htaccess` files:

```
# The "custom prefix" in this case is "webp"
RewriteRule ^webp/(.*)$ plugins/offline/responsiveimages/webp.php?path=$1 [L]
```

### `getWebP` method 

If you want to programmatically generate a WebP thumbnail of a `File` attachment,
you can do so by using the `getWebP` method. It has the same signature as the
default `getThumb` method October provides.

```php
$file = System\Models\File::first();

echo $file->getWebP(200, 'auto');
// http://web.site/plugins/offline/responsiveimages/webp.php?path=path/to/original.jpg
``` 

## Focuspoint

This feature has two components to it:

#### Backend 

In the backend, the file upload widget is extended with a simple focus point selector.

To enable this extension simply set `focuspoint: true` to any fileupload widget in your 
plugin's `fields.yaml`. This feature is off by default. 

Once it is enabled you can click on an uploaded image to select the focus point.

```yaml
fields:
    images:
        label: Images
        mode: image
        useCaption: true
        thumbOptions:
            mode: crop
            extension: auto
        span: full
        type: fileupload
        # Enable the focus point selector
        focuspoint: true
```

![focuspoint-configform](https://user-images.githubusercontent.com/10140882/51920398-97a27480-23e5-11e9-91ee-612da085fdb3.JPG)

#### Frontend

You can use the new `focus` method on any `File` model to get the source to a focus point image.

The `focus` method has the exact same API as the `thumb` method, you can specify a `height`, `width` and a `mode`.

```twig
<img src="{{ image.focus(200, 300, 'auto') }}" alt="">
```

This call will result in the following HTML:


```html
<img src="/storage/temp/public/a9f/2bd/159/offline-focus_30_400_500_50_50_0_0_auto__400.jpg" 
     alt="" 
     class="focuspoint-image" 
     style="width: 100%; height: 100%; object-fit: cover; object-position: 30% 80%;">
``` 

You can disable the injection of the inline styles via the plugin's backend settings.

If you want to use any of the existing focus point JS libraries you can also define a custom container
that will be place around the image. The focus coordinates can be injected as custom `data-*` attributes.

All of these settings are available on the plugin's backend settings page. 

```html
<div class="focuspoint-container" data-focus-x="50" data-focus-y="30">
    <img src="/storage/temp/public/a9f/2bd/159/offline-focus_30_400_500_50_50_0_0_auto__400.jpg" 
         alt="" 
         class="focuspoint-image" 
         data-focus-x="50"
         data-focus-y="30"
     >
 </div>
``` 

### Browser-Compatibility

Be aware that `object-fit` is not supported in IE without
[using a polyfill](https://github.com/bfred-it/object-fit-images).


## Inlining SVG images

This plugin registers a simple `svg` helper function that enables you to inline SVG images from your project.

```twig
<!-- search in theme directory -->
<div class="inline-svg-wrapper">
	{{ svg('assets/icon.svg') }}
</div>

<!-- start with a / to search relative to the project's root -->
<div class="inline-svg-wrapper">
	{{ svg('/plugins/vendor/plugin/assets/icon.svg') }}
</div>
```

### Using variables

Aside from inlining the SVG itself the helper function will also pass any variables
along to the SVG and parse it using October's Twig parser. This means you can
easily create dynamic SVGs.

```svg
<!-- icon.svg -->
<svg fill="{{ fill }}" width="{{ width | default(800) }}"> ...
```

```html
<!-- You can pass variables along as a second parameter -->
<img src="{{ svg('/plugins/xy/assets/icon.svg', {fill: '#f00', width: '200'}) }}">
```

## Bug reports

It is very likely that there will be bugs with some specific html markup. If you encounter such a bug, please report it.

## Future plans

* Exclude/Include-Filters
* Maybe a component to enable the middleware only on some pages

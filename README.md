# ResponsiveImages Plugin for October CMS

Automatically generate and serve images for your visitor's viewport size without changing your theme!


## Features

* [Responsive images](#responsive-images)
* [WebP conversion](#webp-conversion)
* [Focuspoint](#focuspoint)
* [Inline SVG helper function](#inlining-svg-images)

## Responsive images

### How it works

This plugin provides a middleware that adds `srcset` and `sizes` attributes to all locally served images in your html
 response.

It turns this

```html
<img width="500" src="/storage/app/media/image.jpg">
```

into this

```html
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


## Opt-out of processing

If for any reason you want an `img` tag to be completely ignored by this plugin, simply
add the `data-responsive="ignore"` attribute to your tag:

```html
<img src="dont-process-this.jpg" data-responsive="ignore" alt="">
```

## Delete resized images

Use the `php artisan responsive-images:clear` command to delete all
resized images. The next time a page/image is requested, the resized
copies will be created again.

## WebP conversion

This plugin provides an option to serve WebP images if the visiting Browser signals support for it.

Enable the WebP conversion in the backend settings. Enabling this option will add a rule
to your `.htaccess` file, that checks for a `{filename}.{extension}.webp` image for every request
and serves it to the client, if available.

### Console command to convert images to WebP

The plugin includes a handy console command that you can run in a cronjob that iterates
through your storage directories and converts images to WebP:

```
php artisan responsive-images:convert --include=storage/app
```

This command requires the `cwebp` to be available on your server. You can [download it for your OS](https://developers.google.com/speed/webp/download)
and provide the path to the binary using the `--converter-path` option.


#### Conversion errors

You can find a log of failed image conversions in the `offline_responsiveimages_inconvertibles` table.
If the conversion for a file failed, it will be logged in this table. The conversion will not be
retried for files that failed before. To force a retry, remove the file from the table table
and run the conversion command again.


### Support for other servers than Apache

**If you do not use Apache**, you have to configure your server to do the following:

1. Check if `image/webp` is present in the `Accepts` http header
1. Check if the requested image ends in `jp(e)g` or `png`
1. Check if the requested image + a `.webp` exists
1. *If it does*, serve it

### Apache + .htaccess

If you are using Apache with `mod_rewrite` enabled, you do not have to do anything manually.
The plugin [will patch your `.htaccess` file](views/webp-rewrite.htm) if WebP support gets enabled.

### Nginx

If you are using Nginx to serve your October website, you have to modify your server configuration **manually**.

The following example should give you a good starting point.

```nginx
user www-data;

http {
  sendfile on;
  tcp_nopush on;
  tcp_nodelay on;

  include /etc/nginx/mime.types;
  default_type application/octet-stream;

  gzip on;
  gzip_disable "msie6";

  map $http_accept $webp_suffix {
    default   "";
    "~*webp"  ".webp";
  }

  # ...

  server {
    charset utf-8;
    listen 80;

    server_name localhost;
    root /var/www/html;
    index index.php;

    location ~ \.(jpe?g|png)$ {
	add_header Vary Accept;
        try_files $uri$webp_suffix $uri/;
    }

    # ...
  }
}
```

Your `mime.types` file should additionally list the webp mime type:

```types
image/webp  webp;
```

### Other servers

We did not invest in the proper WebP detection configuration for other server software.
Based on the `.htaccess` example above and the [webp-detect](https://github.com/igrigorik/webp-detect)
repo you should be able to figure out what config is needed.

If you have a working example please add it via a PR to this README!

### Custom base directory path

If your application directory is located in a special location, you can set the
`RESPONSIVE_IMAGES_BASE_DIR` environment variable to [change the source path](./webp.php#29).

```htaccess
<IfModule mod_setenvif.c>
    SetEnv RESPONSIVE_IMAGES_BASE_DIR /path/to/your/dir
</IfModule>
```

## Focuspoint

This feature has two components to it:

#### Backend

In the backend, the file upload widget is extended with a simple focus point selector.

To enable this extension simply change the type of any fileupload widget to `focuspoint` in your
plugin's `fields.yaml`. This feature is off by default.

```diff
        avatar:
            label: Avatar
-            type: fileupload
+            type: focuspoint
            mode: image
            imageHeight: 260
            imageWidth: 260
```

Once it is enabled, you can click on an uploaded image to select the focus point.

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

## Upgrading
### Breaking changes in 3.0

If you are using the focuspoint feature, you now have to use `type: focuspoint` to enable the feature.

```diff
image:
-    type: fileupload
-    focuspoint: true
+    type: focuspoint
```

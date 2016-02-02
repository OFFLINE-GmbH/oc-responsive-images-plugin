# ResponsiveImages Plugin for October CMS

## How it works

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

## Configuration

Three image sizes are created by default: 400, 768 and 1024 pixels. 

You can change these values by editing the config/config.php file in the plugin's directory.

### Alternative `src` attribute

If you want to use an alternative `src` attribute you can change this via the `alternative-src` config value. 
This is useful if you are using a plugin like [jQuery.lazyLoad](http://www.appelsiini.net/projects/lazyload) where the image
 is initially linked via a `data-original` attribute.

## Test results

I have tested this plugin on a page with 20 hd wallpapers from pixabay.

| Viewport width | Transferred file size |
| -------------: | ---------------------:|
|        1920 px |               21.8 MB |
|        1024 px |                3.1 MB |
|         768 px |                2.0 MB |
|         400 px |                0.8 MB |

## Bug reports

It is very likely that there will be bugs with some specific html markup. If you encounter such a bug, please report it.

## Future plans

* Exclude/Include-Filters
* Maybe a component to enable the middleware only on some pages
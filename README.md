# ResponsiveImages Plugin for October CMS

Automatically generate and serve images for your visitor's viewport size without changing your theme!

## Focuspoint
With this feature, it is possible to set a focuspoint to responsive images. 

### How it works
This feature serves two major functionalities. One in the October CMS backend, and one in the frontend.
 
#### Backend 
In the backend, it extends the 
fileupload-widget's config-form with two hidden fields for x-axis and y-axis of the image. It calculates its 
percentual space from left and top of a clicked point on the image.
To enable the extension of the fileupload-widget, simply pass `focuspoint: true` to the fileupload-widget of your 
plugin's fields.yaml. By default it will be treated as `false`.

For example: 
```
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
        focuspoint: true
```

##### Example of a config-form with enabled focuspoint:
![focuspoint-configform](https://user-images.githubusercontent.com/10140882/51920398-97a27480-23e5-11e9-91ee-612da085fdb3.JPG)

#### Frontend
The frontend-functionality is able to manipulate the img-node with the individual given 
focuspoint. By default, it will set the x- and y-axis values to object-position CSS-property. To use it in the 
frontend, you can pass `{{ image.focus(width, height) }}` to your twig component (similar like image.thumb()). It 
will then also pass the width and height of the image, which is required for the object-position property.

To make things a little bit easier and codefriendly, there is also a default `class` attribute named 
`focuspoint-image` plus you can put your own class in the settings. Also you are able to switch off the inlined 
sizing-properties.

The default markup then looks like this:
```
<img src="/storage/temp/public/a9f/2bd/159/offline-focus_30_400_500_50_50_0_0_auto__400.jpg" 
srcset="/storage/app/uploads/public/5c3/c4d/1dd/thumb_30_120_0_0_0_auto.jpg 1x, http://base-theme.test/storage/app/uploads/public/5c3/c4d/1dd/thumb_30_240_0_0_0_auto.jpg 2x" 
alt="" 
sizes="(max-width: 400px) 100vw, 400px" 
class=" focuspoint-image" 
style="width: 400px; height: 500px; object-fit: cover; object-position: 50% 50%;">
``` 

##### Frontend-Example with the above set focuspoint from backend
![focuspoint-frontend](https://user-images.githubusercontent.com/10140882/51920548-ed771c80-23e5-11e9-8e1b-4c68448dc26b.JPG)

#### Browser-Compatibility
We're aware that 
the CSS-compatibility isn't yet fully given in several browsers. Therefore, you are also able to store the values to 
own 
named 
`data` attributes and turn off the inline-styling-options in the backend-settings. So you can use own libraries for 
the 
focuspoint.

## Image Rendering
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

### Pre-generate images

You can use the `php artisan responsive-images:generate` command to pre-generate responsive images. The command uses 
October's `pages.menuitem.*` events to build a list of all available URLs and pre-generates all images used on these 
pages. 

### Test results

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
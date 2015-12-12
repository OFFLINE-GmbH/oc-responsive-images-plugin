# Responsive Images Plugin for October CMS

## How it works

This plugin provides a middleware that adds `srcset` and `sizes` attributes to all locally served images in your html
 response.
 
It automatically creates resized copies of the image and serves the most fitting one to your visitor.
  
Currently three image sizes are created: 400, 768 and 1024 pixels. 

Configuration possibilities for these values will be added in a future release.
 
All image copies are saved in your public temp path. Remote file systems are currently untested.

The images are generated on the first page load. Depending on the source image size this may take a few seconds. 
Subsequent page loads will be faster since the images are only resized once.

## Todo

* Unit Tests
* Configuration
* Exclude/Include-Filters
* Maybe a component to enable the middleware only on some pages
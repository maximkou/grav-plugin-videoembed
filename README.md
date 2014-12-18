Embed Video plugin for [Grav CMS](http://getgrav.org)
-------------------------------------------------
[![Build Status](https://travis-ci.org/maximkou/grav-plugin-videoembed.svg?branch=v1.0)](https://travis-ci.org/maximkou/grav-plugin-videoembed)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/maximkou/grav-plugin-videoembed/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/maximkou/grav-plugin-videoembed/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/maximkou/grav-plugin-videoembed/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/maximkou/grav-plugin-videoembed/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/127bf39f-d49a-4c9b-965d-2eb97e384fe4/mini.png)](https://insight.sensiolabs.com/projects/127bf39f-d49a-4c9b-965d-2eb97e384fe4)

This plugin convert links to videos from popular sharing services to embed format. Supported services:

* Youtube
* Vimeo
* Coub.com
* Vine.co
* Custom videos support using VideoJS - http://www.videojs.com
* ... you can propose more services [here](https://github.com/maximkou/grav-plugin-videoembed/issues/7)

## Working example

This `.md` page source:
```
Home
------------
## See this youtube video!

https://www.youtube.com/watch?v=rZudJiJcw3s

```
Will be converted to:
```html
<h1>Home</h1>
<h2>See this youtube video!</h2>
<div>
	<iframe src="//youtube.com/embed/rZudJiJcw3s"></iframe>
</div>
```


## Installation
There are two ways to install plugin:

1. (Recomended) Download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `videoembed`. 
You should now have all the plugin files under `/your/site/grav/user/plugins/videoembed`

2. Simply add plugin dependency into `.dependencies` file, e.g:
```
git:
    videoembed:
        url: https://github.com/maximkou/grav-plugin-videoembed.git
        path: user/plugins/videoembed
        branch: master
```
And then run `php bin/grav install`

## Configuration

All configuration rules located in `videoembed.yaml`

### Plugin settings
#### Disable/enable plugin

You can disable/enable plugin by changing `enabled` option, example:
```
enabled: true # enabled: false for disable
```

#### Responsiveness
Plugin support `responsive` video size in 16:9 ratio. This option disabled by default.
Used [this method for iframes](http://css-tricks.com/NetMag/FluidWidthVideo/Article-FluidWidthVideo.php).
```
responsive: false
```

#### Wrapping embed element
By default, plugin wrap embed element into `div.video-container`, you can change this behaviour by changing `container` option.
```
container:
    element: div # wrapper html element name
    # wrapper element html attributes, like ID, class
    html_attr:
        class: video-container
```
If you have not use wrapper, remove or comment this directive.

**Attention:** Responsiveness option works only with defined container. If `responsive` option enabled and `container` is not defined - you got error.

### Supported services settings
All services configuration located in `services` section of `videoembed.yaml`.

**Available options:**

* *enabled*: disable/enable some service support.
* *all_pages*: disable/enable processing on all pages by deafult
* *assets*: add service-specific assets(`js`, `css`) into page `<HEAD>` block. Assets will be added, if service support enabled and if least one link to service was replaced.
* *embed_html_attr*: html attributes for embed element(iframe/video), e.g. `width: 0` will create `<iframe width="0">...</iframe>`
* *embed_options*: video options, e.g. autoplay (not available for self-hosted videos(`VideoJS`))
* *data_setup (only for `VideoJS`)*: video options, see more [here](https://github.com/videojs/video.js/blob/stable/docs/guides/options.md)

**Default services configuration:**

You can see default services configuration in [videoembed.yaml](https://github.com/maximkou/grav-plugin-videoembed/blob/development/videoembed.yaml#L9) file.

## Enabling and Disabling

If you have **all_pages** option in your configuration set to `true` you can disable `videoembed` on a specific page by adding the following in your page header:

```
---
# ... more headers
videoembed: false
---
```

If you have **all_pages** option in your configuration set to `false` you can enable on a specific page by either providing a `true` value or customization parameters for the `videoembed` option.

## Customizing single video/page videos parameters
If you need set custom plugin parameters for single page, set plugin parameters in page header in section `videoembed`, e.g:

```
---
# ... more headers
videoembed:
    services:
        youtube:
            embed_options:
                fs: 2
---
```

You can override default `embed_options` for each video, for do this simply add params to end of video url. Your params will be applied on default embed options.

Example (using default options), link 
```
http://youtu.be/AsdjHDHksdf?autoplay=0&wmode=transparent
```
will be converted to something like this:

```html
<div class="video-container">
	<iframe src="//youtube.com/embed/AsdjHDHksdf?autoplay=0&rel=0&hd=1&vq=hd1080&wmode=transparent"></iframe>
</div>
```

## License
The MIT License (MIT)

Copyright (c) 2014 Maxim Hodyrev

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

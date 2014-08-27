Embed Video plugin for [Grav CMS](http://getgrav.org)
-------------------------------------------------
[![Build Status](https://travis-ci.org/maximkou/grav-plugin-videoembed.svg?branch=v0.1-alpha)](https://travis-ci.org/maximkou/grav-plugin-videoembed)

This plugin converts video links to embed format.
Supported services:

* Youtube
* Vimeo (planned)

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
Simply add plugin dependency into `.dependencies` file, e.g:
```
git:
    youtube:
        url: https://github.com/maximkou/grav-plugin-videoembed.git
        path: user/plugins/videoembed
        branch: master

links:
    youtube:
        src: grav-plugin-videoembed
        path: user/plugins/videoembed
        scm: github
```

And then run `php bin/grav install`. That's all!

## Configuration

All configuration rules located in `videoembed.yaml`

### Default config

```
enabled: true

# embed element container, if this section empty - not use container
container:
    element: div
    # container element html attributes
    html_attr:
        class: video-container

# supported services configs
services:
    youtube:
        # you can disable support 
        enabled: true
        # embed element html attributes, for youtube element is iframe
        embed_html_attr:
            frameborder: 0
            width      : 560
            height     : 315
        # options, which you will add for videos
        embed_options:
            autoplay   : 1
            autohide   : 1
            fs         : 1
            rel        : 0
            hd         : 1
            vq         : hd1080
            wmode      : opaque
            enablejsapi: 1

```

### Customizing single video/page videos parameters
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

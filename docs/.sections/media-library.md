## Media library
![screenshot](/docs/_media/medialibrary.png)

### Storage provider
The media and files libraries currently support S3, Azure and local storage. Head over to the `twill` configuration file to setup your storage disk and configurations. Also check out the direct upload section of this documentation to setup your IAM users and bucket / container if you want to use S3 or Azure as a storage provider.

### Image rendering service
This package currently ships with 3 rendering services, [Imgix](https://www.imgix.com/), [Glide](http://glide.thephpleague.com/) and a local minimalistic rendering service. It is very simple to implement another one like [Cloudinary](http://cloudinary.com/) or even another local service like or [Croppa](https://github.com/BKWLD/croppa).
Changing the image rendering service can be done by changing the `MEDIA_LIBRARY_IMAGE_SERVICE` environment variable to one of the following options:
- `A17\Twill\Services\MediaLibrary\Glide`
- `A17\Twill\Services\MediaLibrary\Imgix`
- `A17\Twill\Services\MediaLibrary\Local`

For a custom image service you would have to implement the `ImageServiceInterface` and modify your `twill` configuration value `media_library.image_service` with your implementation class.
Here are the methods you would have to implement:

```php
<?php

public function getUrl($id, array $params = []);
public function getUrlWithCrop($id, array $crop_params, array $params = []);
public function getUrlWithFocalCrop($id, array $cropParams, $width, $height, array $params = []);
public function getLQIPUrl($id, array $params = []);
public function getSocialUrl($id, array $params = []);
public function getCmsUrl($id, array $params = []);
public function getRawUrl($id);
public function getDimensions($id);
public function getSocialFallbackUrl();
public function getTransparentFallbackUrl();
```

$crop_params will be an array with the following keys: crop_x, crop_y, crop_w and crop_y. If the service you are implementing doesn't support focal point cropping, you can call the getUrlWithCrop from your implementation.

### Role & crop params
Each _Module_ in your application can have its own predefined image *crops* and *roles*.

A _role_ is a way to define different contexts in which a image might be placed. For example, roles for a `People` model could be `profile` and `cover`. This would allow you to include your People model in list and show a cover image for each, or show an single person model with a profile image. You can associate any number of image roles with your Model.

_Crops_ are more self-explanatory. Twill comes with some pre-defined crop settings to allow you to set different variants of a given image, so crops can be used in combination with _roles_ or they can be used on their own with a single role to define multiple cropping ratios on the same image.

Using the Person example, your `cover` image could have a `square` crop for mobile screens, but could use a `16/9` crop on larger screens. Those values are editable at your convenience for each model, even if there are already some crops created in the CMS.

The only thing you have to do to make it work is to compose your model and repository with the appropriate traits, respectively `HasMedias` and `HandleMedias`, setup your `$mediasParams` configuration and use the `medias` form partial in your form view (more info in the CRUD section).

When it comes to using those data model images in the frontend site, there are a few methods on the `HasMedias` trait that will help you to retrieve them for each of your layouts:

```php
<?php

/**
 * Returns the url of the associated image for $roleName and $cropName.
 * Optionally add params compatible with the current image service in use like w or h.
 * Optionally indicate that you can provide a fallback so that this method will return null
 * instead of the fallback image.
 * Optionally indicate that you are displaying this image in the CMS views.
 * Optionally provide a $media object if you already retrieved one to prevent more SQL requests.
 */
$model->image($roleName, $cropName[, array $params, $has_fallback, $cms, $media])

/**
 * Returns an array of images URLs assiociated with $roleName and $cropName with appended $params.
 */
$model->images($roleName, $cropName[, array $params])

/**
 * Returns the image for $roleName and $cropName with default social image params and $params appended
 */
$model->socialImage($roleName, $cropName[, array $params, $has_fallback])

/**
 * Returns the lqip base64 encoded string from the database for $roleName and $cropName.
 * Use this in conjunction with the RefreshLQIP Artisan command.
 */
$model->lowQualityImagePlaceholder($roleName, $cropName[, array $params, $has_fallback])

/**
 * Returns the image for $roleName and $cropName with default CMS image params and $params appended.
 */
$model->cmsImage($roleName, $cropName[, array $params, $has_fallback])

/**
 * Returns the alt text of the image associated with $roleName.
 */
$model->imageAltText($roleName[, $media])

/**
 * Returns the caption of the image associated with $roleName.
 */
$model->imageCaption($roleName[, $media])

/**
 * Returns the image object associated with $roleName.
 */
$model->imageObject($roleName[, $cropName])

/**
 * Returns the image objects associated with $roleName.
 */
$model->imageObjects($roleName[, $cropName])

/**
 * Returns the image associated with $roleName as an array containing meta information.
 */
$model->imageAsArray($roleName[, $cropName, array $params, $media])

/**
 * Returns the images associated with $roleName as an array containing meta information.
 */
$model->imagesAsArrays($roleName[, $cropName, array $params])

/**
 * Returns an array of images URLs assiociated with $roleName with appended $params for each crop.
 */
$model->imagesWithCrops($roleName[, array $params])

/**
 * Returns the images associated with $roleName as an array containing meta information for each crop.
 */
$model->imagesAsArraysWithCrops($roleName[, array $params])

/**
 * Checks if an image has been attached for the provided role
 */
$model->hasImage($roleName[, $cropName])
```

### File library
The file library is much simpler but also works with S3 and local storage. To associate files to your model, use the `HasFiles` and `HandleFiles` traits, the `$filesParams` configuration and the `files` form partial.

When it comes to using those data model files in the frontend site, there are a few methods on the `HasFiles` trait that will help you to retrieve direct URLs:

```php
<?php

/**
 * Returns the url of the associated file for $roleName.
 * Optionally indicate which locale of the file if your site has multiple languages.
 * Optionally provide a $file object if you already retrieved one to prevent more SQL requests.
 */
$model->file($roleName[, $locale, $file])

/**
 * Returns an array of files URLs assiociated with $roleName.
 */
$model->filesList($roleName[, $locale])

/**
 * Returns the file object associated with $roleName.
 */
$model->fileObject($roleName)
```

### Imgix and S3 direct uploads

On AWS, create a IAM user for full access to your S3 bucket and use its credentials in your `.env` file. You can use the following IAM permission:

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": "s3:*",
            "Resource": [
                "arn:aws:s3:::YOUR_BUCKER_IDENTIFIER/*",
                "arn:aws:s3:::YOUR_BUCKER_IDENTIFIER"
            ]
        }
    ]
}
```

Create another IAM user for Imgix with read-only access to your bucket and use its credentials to create an S3 source on [Imgix](https://imgix.com). You can use the following IAM permission:

```json
{
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:GetObject",
                "s3:ListBucket",
                "s3:GetBucketLocation"
            ],
            "Resource": [
                "arn:aws:s3:::YOUR_BUCKER_IDENTIFIER/*",
                "arn:aws:s3:::YOUR_BUCKER_IDENTIFIER"
            ]
        }
    ]
}
```

For improved security, modify the S3 bucket CORS configuration to accept uploads request from your admin domain only:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<CORSConfiguration xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
    <CORSRule>
        <AllowedOrigin>https://YOUR_ADMIN_DOMAIN</AllowedOrigin>
        <AllowedOrigin>http://YOUR_ADMIN_DOMAIN</AllowedOrigin>
        <AllowedMethod>POST</AllowedMethod>
        <AllowedMethod>PUT</AllowedMethod>
        <AllowedMethod>DELETE</AllowedMethod>
        <MaxAgeSeconds>3000</MaxAgeSeconds>
        <ExposeHeader>ETag</ExposeHeader>
        <AllowedHeader>*</AllowedHeader>
    </CORSRule>
</CORSConfiguration>
```

### Imgix and local uploads

When setting up an Imgix source for local uploads, choose the `Web Folder` source type and specify your domain in the `Base URL` settings.

![screenshot](/docs/_media/imgix_source.png)

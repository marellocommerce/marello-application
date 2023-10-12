<?php

namespace Marello\Bundle\CoreBundle\EmbeddedImages;

use Oro\Bundle\EmailBundle\EmbeddedImages\EmbeddedImage;
use Oro\Bundle\EmailBundle\EmbeddedImages\EmbeddedImagesExtractor as BaseEmbeddedImagesExtractor;
use Symfony\Component\Mime\MimeTypesInterface;

class EmbeddedImagesExtractor extends BaseEmbeddedImagesExtractor
{
    private MimeTypesInterface $mimeTypes;

    public function __construct(MimeTypesInterface $mimeTypes)
    {
        $this->mimeTypes = $mimeTypes;
    }

    public function extractEmbeddedImages(string &$content): array
    {
        $embeddedImages = [];
        $content = preg_replace_callback(
            '/<img(?P<attrs>.*)src(?:\s*)=(?:\s*)["\'](?P<src>.*)["\']/U',
            function ($matches) use (&$embeddedImages) {
                if (!empty($matches['src'])) {
                    if (str_starts_with($matches['src'], 'data:image')) {
                        [$mime, $data] = explode(';', $matches['src']);
                        [$encoding, $encodedContent] = explode(',', $data);
                        $mime = str_replace('data:', '', $mime);
                        $extensions = $this->mimeTypes->getExtensions($mime);
                        $fileName = sprintf('%s.%s', uniqid('', true), \array_shift($extensions));

                        $embeddedImages[$fileName] = new EmbeddedImage(
                            $encodedContent,
                            $fileName,
                            $mime,
                            $encoding
                        );

                        return sprintf('<img%ssrc="cid:%s"', $matches['attrs'], $fileName);
                    } else {
                        return sprintf('<img%ssrc="%s"', $matches['attrs'], $matches['src']);
                    }
                }

                return $matches[0];
            },
            $content
        );

        return $embeddedImages;
    }
}

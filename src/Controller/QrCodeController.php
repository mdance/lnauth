<?php

namespace Drupal\lnauth\Controller;

use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides the QrCodeController class.
 */
class QrCodeController extends ControllerBase {

  /**
   * Displays a QR Code.
   *
   * @param string $data
   *   Provides the data.
   *
   * @return mixed
   *   The QR code image.
   */
  public function image($data = '', ?Request $request = NULL) {
    $data = $request->query->get('data', $data);

    $style = new RendererStyle(300);
    $backend = new ImagickImageBackEnd();

    $renderer = new ImageRenderer($style, $backend);

    $writer = new Writer($renderer);

    $content = $writer->writeString($data);

    if ($content) {
      $headers = [];

      $headers['Content-type'] = 'image/png';

      return new Response($content, 200, $headers);
    }

    $message = 'An error occurred generating the QR code';

    throw new NotFoundHttpException($message);
  }

}

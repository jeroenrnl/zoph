<?php
/**
 * HTML MIME Mail composer class
 *
 *  @copyright 2002-2003  Richard Heyes
 *  @copyright 2003-2005  The PHP Group
 *  All rights reserved.
 *
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions
 *  are met:
 *
 *  o Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *  o Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *  o The names of the authors may not be used to endorse or promote
 *    products derived from this software without specific prior written
 *    permission.
 *
 *  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 *  "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 *  LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 *  A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 *  OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 *  SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 *  LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 *  DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 *  THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 *  OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Tobias Ratschiller <tobias@dnet.it> and
 * @author Sascha Schumann <sascha@schumann.cx>
 * @author Richard Heyes <richard@phpguru.org>
 * @author Tomas V.V.Cox <cox@idecnet.com> (port to PEAR)
 *
 * @author Jeroen Roos
 *
 * @package Zoph
 */

/**
 * Mime mail composer class. Can handle: text and html bodies, embedded html
 * images and attachments.
 * @author Tobias Ratschiller <tobias@dnet.it> and
 * @author Sascha Schumann <sascha@schumann.cx>
 * @author Richard Heyes <richard@phpguru.org>
 * @author Tomas V.V.Cox <cox@idecnet.com> (port to PEAR)
 *
 * @author Jeroen Roos
 *
 * @package Zoph
 */
class MailMime {
    /** @var string Contains the plain text part of the email */
    private $txtbody;
    /** @var string Contains the html part of the email */
    private $htmlbody;
    /** @var array list of the attached images */
    private $html_images = array();
    /** @var array list of the attachments */
    private $parts = array();
    /** @var array Build parameters */
    private $build_params = array();
    /** @var array Headers for the mail */
    private $headers = array();

    /**
     * Constructor function
     */
    public function __construct() {
        $this->build_params = array(
            'text_encoding' => '7bit',
            'html_encoding' => 'quoted-printable',
            '7bit_wrap'     => 998,
            'html_charset'  => 'utf-8',
            'text_charset'  => 'utf-8',
            'head_charset'  => 'utf-8'
        );
    }

    /**
     * Accessor function to set the body text. Body text is used if
     * it's not an html mail being sent or else is used to fill the
     * text/plain part that emails clients who don't support
     * html should show.
     *
     * @param  string  Either a string or the file name with the contents
     * @param  bool If true the first param should be treated as a file name,
     *              else as a string (default)
     * @param  bool If true the text or file is appended to the existing body,
     *              else the old body is overwritten
     * @return bool true on success
     */
    public function setTXTBody($data, $isfile = false, $append = false) {
        if (!$isfile) {
            if (!$append) {
                $this->txtbody = $data;
            } else {
                $this->txtbody .= $data;
            }
        } else {
            $cont = $this->file2str($data);
            if (!$append) {
                $this->txtbody = $cont;
            } else {
                $this->txtbody .= $cont;
            }
        }
        return true;
    }

    /**
     * Adds a html part to the mail
     *
     * @param  string Either a string or the file name with the contents
     * @param  bool If true the first param should be treated as a file name,
     *              else as a string (default)
     * @return bool true on succes
     */
    public function setHTMLBody($data, $isfile = false) {
        if (!$isfile) {
            $this->htmlbody = $data;
        } else {
            $cont = $this->file2str($data);
            $this->htmlbody = $cont;
        }

        return true;
    }

    /**
     * Adds an image to the list of embedded images. The source is a string containing the image.
     *
     * @paramstring The image data.
     * @param string The file name
     * @param string The content type
     * @return bool true
     */
    public function addHTMLImageFromString($filedata, $filename,
            $c_type='application/octet-stream') {
        $filename = basename($filename);
        $this->html_images[] = array(
            'body'   => $filedata,
            'name'   => $filename,
            'c_type' => $c_type,
            'cid'    => md5(uniqid(time()))
        );
        return true;
    }

    /**
     * Adds an image to the list of embedded images. The source is a file on disk.
     *
     * @param string The file to be used as attachment
     * @param string The content type
     * @param string encoding.
     */
    public function addHTMLImageFromFile($file,
            $c_type='application/octet-stream') {
        $filedata = $this->file2str($file);
        return $this->addHTMLImageFromString($filedata, $file, $c_type);
    }

    /**
     * Adds a file to the list of attachments. The source is a string containing the
     * contents of the file.
     *
     * @param string The file data to use as attachment
     * @param string The content type
     * @param string The filename of the attachment.
     * @param string encoding.
     * @throws MailException
     */
    public function addAttachmentFromString($filedata, $filename,
            $c_type = 'application/octet-stream', $encoding = 'base64') {
        if (empty($filename)) {
            throw new MailException("The supplied filename for the attachment can\'t be empty");
        }
        $filename = basename($filename);
        $this->parts[] = array(
            'body'     => $filedata,
            'name'     => $filename,
            'c_type'   => $c_type,
            'encoding' => $encoding
        );
    }

    /**
     * Adds a file to the list of attachments. The source is a file on disk.
     *
     * @param string The file to be used as attachment
     * @param string The content type
     * @param string encoding.
     */
    public function addAttachmentFromFile($file,
            $c_type = 'application/octet-stream', $encoding = 'base64') {
        $filedata=$this->file2str($file);
        $this->addAttachmentFromString($filedata, $file, $c_type, $encoding);
    }


    /**
     * Get the contents of the given file name as string
     *
     * @param  string  path of file to process
     * @return string  contents of $file_name
     * @throws mailException
     */
    private function file2str($file_name) {
        if (!is_readable($file_name)) {
            throw new MailException('File is not readable ' . $file_name);
        }
        if (!$fd = fopen($file_name, 'rb')) {
            throw new MailException('Could not open ' . $file_name);
        }
        $filesize = filesize($file_name);
        if ($filesize == 0){
            $cont =  "";
        }else{
            $cont = fread($fd, $filesize);
        }
        fclose($fd);
        return $cont;
    }

    /**
     * Adds a text subpart to the mailMimePart object and
     * returns it during the build process.
     *
     * @param mixed The object to add the part to, or null if a new object is to be created.
     * @param string   The text to add.
     * @return mailMimePart The text mailMimePart object
     */
    private function addTextPart($obj, $text) {
        $params['content_type'] = 'text/plain';
        $params['encoding']     = $this->build_params['text_encoding'];
        $params['charset']      = $this->build_params['text_charset'];
        if (is_object($obj)) {
            return $obj->addSubpart($text, $params);
        } else {
            return new mailMimePart($text, $params);
        }
    }

    /**
     * Adds a html subpart to the mailMimePart object and
     * returns it during the build process.
     *
     * @param  mixed   The object to add the part to, or null if a new object is to be created.
     * @return mailMimePart The html mailMimePart object
     */
    private function addHtmlPart($obj) {
        $params['content_type'] = 'text/html';
        $params['encoding']     = $this->build_params['html_encoding'];
        $params['charset']      = $this->build_params['html_charset'];
        if (is_object($obj)) {
            return $obj->addSubpart($this->htmlbody, $params);
        } else {
            return new mailMimePart($this->htmlbody, $params);
        }
    }

    /**
     * Creates a new mimePart object, using multipart/mixed as
     * the initial content-type and returns it during the
     * build process.
     *
     * @return mailMimePart  The multipart/mixed mailMimePart object
     */
    private function addMixedPart() {
        $params['content_type'] = 'multipart/mixed';
        return new mailMimePart('', $params);
    }

    /**
     * Adds a multipart/alternative part to a mimePart
     * object (or creates one), and returns it during
     * the build process.
     *
     * @param  mixed   The object to add the part to, or
     *                 null if a new object is to be created.
     * @return mailMimePart  The multipart/mixed mailMimePart object
     */
    private function addAlternativePart($obj) {
        $params['content_type'] = 'multipart/alternative';
        if (is_object($obj)) {
            return $obj->addSubpart('', $params);
        } else {
            return new mailMimePart('', $params);
        }
    }

    /**
     * Adds a multipart/related part to a mailMimePart
     * object (or creates one), and returns it during
     * the build process.
     *
     * @param mixed    The object to add the part to, or
     *                 null if a new object is to be created
     * @return mailMimePart  The multipart/mixed mimePart object
     */
    private function addRelatedPart($obj) {
        $params['content_type'] = 'multipart/related';
        if (is_object($obj)) {
            return $obj->addSubpart('', $params);
        } else {
            return new mailMimePart('', $params);
        }
    }

    /**
     * Adds an html image subpart to a mailMimePart object
     * and returns it during the build process.
     *
     * @param  mailMimePart The mailMimePart to add the image to
     * @param  array   The image information
     * @return mailMimePart  The image mailMimePart object
     */
    private function addHtmlImagePart(mailMimePart $obj, $value) {
        $params['content_type'] = $value['c_type'];
        $params['encoding']     = 'base64';
        $params['disposition']  = 'inline';
        $params['dfilename']    = $value['name'];
        $params['cid']          = $value['cid'];
        $obj->addSubpart($value['body'], $params);
    }

    /**
     * Adds an attachment subpart to a mailMimePart object
     * and returns it during the build process.
     *
     * @param  mailMimePart  The mailMimePart to add the image to
     * @param  array   The attachment information
     * @return mailMimePart  The image mailMimePart object
     */
    private function addAttachmentPart(mailMimePart $obj, $value) {
        $params['content_type'] = $value['c_type'];
        $params['encoding']     = $value['encoding'];
        $params['disposition']  = 'attachment';
        $params['dfilename']    = $value['name'];
        $obj->addSubpart($value['body'], $params);
    }

    /**
     * Builds the multipart message from the list ($this->parts) and
     * returns the mime content.
     *
     * @param  array  Build parameters that change the way the email
     *                is built. Should be associative. Can contain:
     *                text_encoding  -  What encoding to use for plain text
     *                                  Default is 7bit
     *                html_encoding  -  What encoding to use for html
     *                                  Default is quoted-printable
     *                7bit_wrap      -  Number of characters before text is
     *                                  wrapped in 7bit encoding
     *                                  Default is 998
     *                html_charset   -  The character set to use for html.
     *                                  Default is iso-8859-1
     *                text_charset   -  The character set to use for text.
     *                                  Default is iso-8859-1
     *                head_charset   -  The character set to use for headers.
     *                                  Default is iso-8859-1
     * @return string The mime content
     */
    public function get($build_params = null) {
        if (isset($build_params)) {
            while (list($key, $value) = each($build_params)) {
                $this->build_params[$key] = $value;
            }
        }

        if (!empty($this->html_images) AND isset($this->htmlbody)) {
            foreach ($this->html_images as $value) {
                $regex = '#(\s)((?i)src|background|href(?-i))\s*=\s*(["\']?)' .
                    preg_quote($value['name'], '#') .  '\3#';
                $rep = '\1\2=\3cid:' . $value['cid'] .'\3';
                $this->htmlbody = preg_replace($regex, $rep,
                                       $this->htmlbody
                                   );
            }
        }

        $null        = null;
        $attachments = !empty($this->parts)                ? true : false;
        $html_images = !empty($this->html_images)          ? true : false;
        $html        = !empty($this->htmlbody)             ? true : false;
        $text        = (!$html AND !empty($this->txtbody)) ? true : false;

        switch (true) {
        case $text AND !$attachments:
            $message = $this->addTextPart($null, $this->txtbody);
            break;

        case !$text AND !$html AND $attachments:
            $message = $this->addMixedPart();
            foreach ($this->parts as $part) {
                $this->addAttachmentPart($message, $part);
            }
            break;

        case $text AND $attachments:
            $message = $this->addMixedPart();
            $this->addTextPart($message, $this->txtbody);
            foreach ($this->parts as $part) {
                $this->addAttachmentPart($message, $part);
            }
            break;

        case $html AND !$attachments AND !$html_images:
            if (isset($this->txtbody)) {
                $message = $this->addAlternativePart($null);
                $this->addTextPart($message, $this->txtbody);
                $this->addHtmlPart($message);
            } else {
                $message =$this->addHtmlPart($null);
            }
            break;

        case $html AND !$attachments AND $html_images:
            if (isset($this->txtbody)) {
                $message =$this->addAlternativePart($null);
                $this->addTextPart($message, $this->txtbody);
                $related = $this->addRelatedPart($message);
            } else {
                $message = $this->addRelatedPart($null);
                $related = $message;
            }
            $this->addHtmlPart($related);
            foreach ($this->html_images as $img) {
                $this->addHtmlImagePart($related, $img);
            }
            break;

        case $html AND $attachments AND !$html_images:
            $message = $this->addMixedPart();
            if (isset($this->txtbody)) {
                $alt = $this->addAlternativePart($message);
                $this->addTextPart($alt, $this->txtbody);
                $this->addHtmlPart($alt);
            } else {
                $this->addHtmlPart($message);
            }
            foreach ($this->parts as $part) {
                $this->addAttachmentPart($message, $part);
            }
            break;

        case $html AND $attachments AND $html_images:
            $message = $this->addMixedPart();
            if (isset($this->txtbody)) {
                $alt = $this->addAlternativePart($message);
                $this->addTextPart($alt, $this->txtbody);
                $rel = $this->addRelatedPart($alt);
            } else {
                $rel = $this->addRelatedPart($message);
            }
            $this->addHtmlPart($rel);
            foreach ($this->html_images as $img) {
                $this->addHtmlImagePart($rel, $img);
            }
            foreach ($this->parts as $part) {
                $this->addAttachmentPart($message, $part);
            }
            break;

        }

        if (isset($message)) {
            $output = $message->encode();
            $this->headers = array_merge($this->headers,
                                          $output['headers']);
            return $output['body'];

        } else {
            return false;
        }
    }

    /**
     * Returns an array with the headers needed to prepend to the email
     * (MIME-Version and Content-Type). Format of argument is:
     * $array['header-name'] = 'header-value';
     *
     * @param  array Assoc array with any extra headers.  Optional.
     * @return array Assoc array with the mime headers
     */
    public function headers(array $xtra_headers = null) {
        // Content-Type header should already be present,
        // So just add mime version header
        $headers['MIME-Version'] = '1.0';
        if (isset($xtra_headers)) {
            $headers = array_merge($headers, $xtra_headers);
        }
        $this->headers = array_merge($headers, $this->headers);

        return $this->encodeHeaders($this->headers);
    }

    /**
     * Get the text version of the headers
     * (useful if you want to use the PHP mail() function)
     *
     * @param  array headers Assoc array with any extra headers. Optional.
     * @return string  Plain text headers
     */
    public function txtHeaders(array $xtra_headers = null) {
        $headers = $this->headers($xtra_headers);
        $ret = '';
        foreach ($headers as $key => $val) {
            $ret .= "$key: $val" . PHP_EOL;
        }
        return $ret;
    }

    /**
     * Sets the Subject header
     *
     * @param  string $subject String to set the subject to
     */
    public function setSubject($subject) {
        $this->headers['Subject'] = $subject;
    }

    /**
     * Set an email to the From (the sender) header
     *
     * @param  string $email The email address to add
     */
    public function setFrom($email) {
        $this->headers['From'] = $email;
    }

    /**
     * Add an email to the Cc (carbon copy) header
     * (multiple calls to this method are allowed)
     *
     * @param  string The email address to add
     */
    public function addCc($email) {
        if (isset($this->headers['Cc'])) {
            $this->headers['Cc'] .= ", $email";
        } else {
            $this->headers['Cc'] = $email;
        }
    }

    /**
     * Add an email to the Bcc (blind carbon copy) header
     * (multiple calls to this method are allowed)
     *
     * @param  string The email address to add
     */
    public function addBcc($email) {
        if (isset($this->headers['Bcc'])) {
            $this->headers['Bcc'] .= ", $email";
        } else {
            $this->headers['Bcc'] = $email;
        }
    }

    /**
     * Encodes a header as per RFC2047
     *
     * @param  string  The header data to encode
     * @return string  Encoded data
     */
    private function encodeHeaders($input) {
        foreach ($input as $hdr_name => $hdr_value) {
            preg_match_all('/(\w*[\x80-\xFF]+\w*)/', $hdr_value, $matches);
            foreach ($matches[1] as $value) {
                $replacement = preg_replace('/([\x80-\xFF])/e',
                                            '"=" .
                                            strtoupper(dechex(ord("\1")))',
                                            $value);
                $hdr_value = str_replace($value, '=?' .
                                         $this->build_params['head_charset'] .
                                         '?Q?' . $replacement . '?=',
                                         $hdr_value);
            }
            $input[$hdr_name] = $hdr_value;
        }

        return $input;
    }

} // End of class
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
?>

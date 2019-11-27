<?php
namespace Vidal\MainBundle\Service;

class Mailer extends \PHPMailer {
    /**
     * Save email to a folder (via IMAP)
     *
     * This function will open an IMAP stream using the email
     * credentials previously specified, and will save the email
     * to a specified folder. Parameter is the folder name (ie, Sent)
     * if nothing was specified it will be saved in the inbox.
     *
     * @author David Tkachuk <http://davidrockin.com/>
     */
    public function copyToFolder($folderPath = null) {
        $message = $this->MIMEHeader . $this->MIMEBody;
        $path = "INBOX" . (isset($folderPath) && !is_null($folderPath) ? ".".$folderPath : ""); // Location to save the email
        $imapStream = imap_open("{" . $this->Host . "}" . $path , $this->Username, $this->Password);

        imap_append($imapStream, "{" . $this->Host . "}" . $path, $message);
        imap_close($imapStream);
    }
}
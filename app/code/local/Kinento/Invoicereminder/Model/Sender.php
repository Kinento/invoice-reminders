<?php
/**
 * Kinento Invoice Reminders
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @category   Kinento
 * @package    Kinento_Invoicereminder
 * @copyright  Copyright (c) 2009-2015 Kinento
 * @license    MIT license
 *
 */

class Kinento_Invoicereminder_Model_Sender extends Mage_Core_Model_Abstract {

	// Mailer constant path
	const XML_PATH_EMAIL_SENDER = 'contacts/email/sender_email_identity';

	// Main function to decide whether or not to send mail
	public function prepareMail() {

		// Get the current date
		$now = new Zend_Date( Mage::getModel( 'core/date' )->gmtTimestamp() );

		// Get the setting for the start of history
		$history = new Zend_Date( Mage::getStoreConfig( 'invoicereminder/generalsettings/startingdate' ) );

		// Get settings for the status and payment method filters
		$statusfilter = explode( ',', Mage::getStoreConfig( 'invoicereminder/generalsettings/invoicestatuses' ) );

		// Get the data from the Magento database
		$collection = Mage::getResourceModel( 'sales/order_invoice_grid_collection' )
			->addAttributeToSelect( '*' )
			->addAttributeToFilter( 'state', array( 'in' => $statusfilter ) )
			->addAttributeToFilter( 'created_at', array( 'from' => $history, 'to' => $now, 'datetime'=>true ) );

		// Iterate over all the invoices
		$invoiceremindermodel = Mage::getModel( 'invoicereminder/invoicereminder' );
		$invoices = $collection->getItems();
		foreach ( $invoices as $invoice ) {

			// Load the corresponding order
			$order = Mage::getModel( 'sales/order' )->loadByIncrementId( $invoice->getOrderIncrementId() );

			// Get settings for the payment filters
			$paymentfilter = explode( ',', Mage::getStoreConfig( 'invoicereminder/generalsettings/invoicepayments', $order->getStoreId() ) );

			// Get settings for onaccount customers
			$firstnotificationonaccount = Mage::getStoreConfig( 'invoicereminder/timesettings/firstnotificationonaccount', $order->getStoreId() );
			$firstonaccount = new Zend_Date( Mage::getModel( 'core/date' )->gmtTimestamp() );
			$firstonaccount = $firstonaccount->subDay( $firstnotificationonaccount );
			$secondnotificationonaccount = Mage::getStoreConfig( 'invoicereminder/timesettings/secondnotificationonaccount', $order->getStoreId() );
			$secondonaccount = new Zend_Date( Mage::getModel( 'core/date' )->gmtTimestamp() );
			$secondonaccount = $secondonaccount->subDay( $secondnotificationonaccount );
			$nthonaccount = Mage::getStoreConfig( 'invoicereminder/timesettings/nthonaccount', $order->getStoreId() );

			// Get settings for prepaid customers
			$firstnotificationprepaid = Mage::getStoreConfig( 'invoicereminder/timesettings/firstnotificationprepaid', $order->getStoreId() );
			$firstprepaid = new Zend_Date( Mage::getModel( 'core/date' )->gmtTimestamp() );
			$firstprepaid = $firstprepaid->subDay( $firstnotificationprepaid );
			$secondnotificationprepaid = Mage::getStoreConfig( 'invoicereminder/timesettings/secondnotificationprepaid', $order->getStoreId() );
			$secondprepaid = new Zend_Date( Mage::getModel( 'core/date' )->gmtTimestamp() );
			$secondprepaid = $secondprepaid->subDay( $secondnotificationprepaid );
			$nthprepaid = Mage::getStoreConfig( 'invoicereminder/timesettings/nthprepaid', $order->getStoreId() );

			// Get the invoice from the 'invoicereminder' database
			$reminderinvoices = $invoiceremindermodel->getCollection()->addFieldToFilter( 'increment_id', $invoice->getIncrementId() )->getItems();

			// If it doesn't exist yet, create it
			if ( empty( $reminderinvoices ) ) {
				$this->createInvoicereminderEntry( $invoice, $invoiceremindermodel );
				$reminderinvoices = $invoiceremindermodel->getCollection()->addFieldToFilter( 'increment_id', $invoice->getIncrementId() )->getItems();
			}

			// Process the invoice as present in the 'invoicereminder' database
			foreach ( $reminderinvoices as $reminderinvoice ) {

				// Make sure that the invoice is not filtered out - otherwise don't do any processing
				if ( $reminderinvoice->getStatus() == 'enabled' &&
					$order->getBillingAddress() != null &&
					in_array( $order->getPayment()->getMethod(), $paymentfilter ) ) {

					// Get the date of the invoice
					$invoicedate = strtotime( $invoice->getCreatedAt() );

					//  Find out if the invoice is part of the on-account customers
					$onaccount = Mage::getStoreConfig( 'invoicereminder/generalsettings/groupsonaccount', $order->getStoreId() );
					if ( in_array( $order->getCustomerGroupId(), explode( ',', $onaccount ) ) ) {

						// First reminder for on-account customers
						if ( $reminderinvoice->getInvoicereminders() == 0 ) {
							if ( $firstonaccount->getTimestamp() > $invoicedate ) {
								$this->prepareReminder( $invoice, $invoiceremindermodel, $order );
							}
						}

						// Second reminder for on-account customers
						elseif ( $reminderinvoice->getInvoicereminders() == 1 ) {
							if ( $secondonaccount->getTimestamp() > $invoicedate ) {
								$this->prepareReminder( $invoice, $invoiceremindermodel, $order );
							}
						}

						// Third (or fourth, fifth, etc.) reminder for on-account customers
						else {
							$newdate = new Zend_Date( $secondonaccount );
							$limit = $newdate->subDay( ( $reminderinvoice->getInvoicereminders()-1 )*$nthonaccount );
							if ( $limit->getTimestamp() > $invoicedate ) {
								$this->prepareReminder( $invoice, $invoiceremindermodel, $order );
							}
						}
					}

					// The invoice was not on-account, it must be in the prepaid customers group
					else {

						// First reminder for prepaid customers
						if ( $reminderinvoice->getInvoicereminders() == 0 ) {
							if ( $firstprepaid->getTimestamp() > $invoicedate ) {
								$this->prepareReminder( $invoice, $invoiceremindermodel, $order );
							}
						}

						// Second reminder for prepaid customers
						elseif ( $reminderinvoice->getInvoicereminders() == 1 ) {
							if ( $secondprepaid->getTimestamp() > $invoicedate ) {
								$this->prepareReminder( $invoice, $invoiceremindermodel, $order );
							}
						}

						// Third (or fourth, fifth, etc.) reminder for prepaid customers
						else {
							$newdate = new Zend_Date( $secondprepaid );
							$limit = $newdate->subDay( ( $reminderinvoice->getInvoicereminders()-1 )*$nthprepaid );
							if ( $limit->getTimestamp() > $invoicedate ) {
								$this->prepareReminder( $invoice, $invoiceremindermodel, $order );
							}
						}
					}
				}
			}
		}
	}

	// Function to send an email independent of any settings or filters
	public function manualMail( $id ) {

		// Get the data from the Magento database
		$collection = Mage::getResourceModel( 'sales/order_invoice_grid_collection' )
			->addAttributeToSelect( '*' )
			->addAttributeToFilter( 'increment_id', $id );

		// Iterate over all the invoices
		$invoiceremindermodel = Mage::getModel( 'invoicereminder/invoicereminder' );
		$invoices = $collection->getItems();

		foreach ( $invoices as $invoice ) {

			// Load the corresponding order
			$order = Mage::getModel( 'sales/order' )->loadByIncrementId( $invoice->getOrderIncrementId() );

			// Get the invoice from the 'invoicereminder' database
			$reminderinvoices = $invoiceremindermodel->getCollection()->addFieldToFilter( 'increment_id', $id )->getItems();

			// If it doesn't exist yet, create it
			if ( empty( $reminderinvoices ) ) {
				$this->createInvoicereminderEntry( $invoice, $invoiceremindermodel );
				$reminderinvoices = $invoiceremindermodel->getCollection()->addFieldToFilter( 'increment_id', $id )->getItems();
			}

			//  Find out if the invoice is part of the on-account customers
			$onaccount = Mage::getStoreConfig( 'invoicereminder/generalsettings/groupsonaccount', $order->getStoreId() );

			// Send out reminder for on-account customers
			if ( in_array( $order->getCustomerGroupId(), explode( ',', $onaccount ) ) ) {
				$this->prepareReminder( $invoice, $invoiceremindermodel, $order );
			}

			// Send out reminder for prepaid customers
			else {
				$this->prepareReminder( $invoice, $invoiceremindermodel, $order );
			}
		}
	}

	// Function to create an entry in the 'invoicereminder' database
	public function createInvoicereminderEntry( $invoice, $invoiceremindermodel ) {
		$data = array(
			"increment_id"  => $invoice->getIncrementId(),
			"invoicereminders"  => 0,
		);
		$invoiceremindermodel->setData( $data );
		$invoiceremindermodel->save();
	}

	// Function to prepare a reminder email
	public function prepareReminder( $invoice, $invoiceremindermodel, $order ) {

		// Gather all necessary data
		$data = array(
			"invoice"      => $invoice,
			"order"            => $order,
			"payment"          => $order->getPayment(),
			"customername"     => $order->getShippingName(),
			"orderid"          => $order->getIncrementId(),
			"orderincrementid" => $order->getIncrementId(),
			"invoiceincrementid" => $invoice->getIncrementId(),
			"orderdate"        => $order->getCreatedAt(),
			"invoicedate"      => $invoice->getCreatedAt(),
			"orderamount"      => money_format( "%n", $order->getGrandTotal() ),
			"invoiceamount"    => money_format( "%n", $invoice->getGrandTotal() ),
			"customeremail"    => $order->getCustomerEmail(),
			"invoices"         => $order->getInvoiceCollection()->getItems(),
			"storeid"          => $order->getStoreId(),
			"paymentmethod"    => $order->getPayment()->getMethod(),
		);

		// Add the invoice ID to the emails
		if ( !empty( $data["invoices"] ) ) {
			$data["invoiceid"] = reset( $data["invoices"] )->getIncrementId();
		}

		// Additional if-statement for a bug with email addresses, it seems with Magento 1.4.1.0 (unsure about other versions)
		if ( $data["customeremail"] == "" ) {
			$order_bis = Mage::getModel( 'sales/order' )->loadByIncrementId( $data["orderincrementid"] );
			$data["customeremail"] = $order_bis->getCustomerEmail();
		}

		// Set additional data depending on on-account or prepaid
		$onaccount = Mage::getStoreConfig( 'invoicereminder/generalsettings/groupsonaccount', $order->getStoreId() );
		if ( in_array( $invoice->getCustomerGroupId(), explode( ',', $onaccount ) ) ) {
			$data["customergroup"] = 'On account';
			$data["customergroupdata"] = Mage::getStoreConfig( 'invoicereminder/emailsettings/paytypeone', $order->getStoreId() );
			$data["attachment"] = Mage::getStoreConfig( 'invoicereminder/emailsettings/attachonaccount', $order->getStoreId() );
		}
		else {
			$data["customergroup"] = 'Prepaid';
			$data["customergroupdata"] = Mage::getStoreConfig( 'invoicereminder/emailsettings/paytypetwo', $order->getStoreId() );
			$data["attachment"] = Mage::getStoreConfig( 'invoicereminder/emailsettings/attachprepaid', $order->getStoreId() );
		}

		// Set additional data depending on normal or alternative payment method
		$normalpayments = Mage::getStoreConfig( 'invoicereminder/emailsettings/altpayments', $order->getStoreId() );
		if ( in_array( $order->getPayment()->getMethod(), explode( ',', $normalpayments ) ) ) {
			$data["paymenttype"] = 'Normal';
			$data["paymenttypedata"] = Mage::getStoreConfig( 'invoicereminder/emailsettings/normaltext', $order->getStoreId() );
		}
		else {
			$data["paymenttype"] = 'Alternative';
			$data["paymenttypedata"] = Mage::getStoreConfig( 'invoicereminder/emailsettings/alttext', $order->getStoreId() );
		}

		// Update the data in the 'invoicereminder' database and send out the reminder
		$reminderinvoices = $invoiceremindermodel->getCollection()->addFieldToFilter( 'increment_id', $data["invoiceincrementid"] )->getItems();
		foreach ( $reminderinvoices as $reminderinvoice ) {
			$data["remindercount"] = $reminderinvoice->getInvoicereminders()+1;
			$this->preSendReminder( $data, $invoice, $order );
			$reminderinvoice->setInvoicereminders( $data["remindercount"] );
			$reminderinvoice->save();
		}
	}

	// Function to send either just the reminder or also a copy
	public function preSendReminder( $data, $invoice, $order ) {

		// Find out if we need to send a copy
		$copy = Mage::getStoreConfig( 'invoicereminder/emailsettings/emailcopy', $order->getStoreId() );

		// Send the copy to the specified email address
		if ( $copy != "" ) {
			$this->sendReminder( $data, $invoice, $copy );
		}

		// Send the original reminder
		$this->sendReminder( $data, $invoice, $data["customeremail"] );
	}

	// Function to send the actual email reminder
	public function sendReminder( $data, $invoice, $emailaddress ) {

		// Set-up the email environment
		$translate = Mage::getSingleton( 'core/translate' );
		$translate->setTranslateInline( false );
		$mail = Mage::getModel( 'core/email_template' );

		// Get the reminder email templates
		if ( $data["remindercount"] == 1 ) {
			$template = Mage::getStoreConfig( 'invoicereminder/emailsettings/templateone', $invoice->getStoreId() );
		}
		elseif ( $data["remindercount"] == 2 ) {
			$template = Mage::getStoreConfig( 'invoicereminder/emailsettings/templatetwo', $invoice->getStoreId() );
		}
		else {
			$template = Mage::getStoreConfig( 'invoicereminder/emailsettings/templatethree', $invoice->getStoreId() );
		}

		// Get the attachment if it is enabled in the settings
		if ( $data["attachment"] == 'enabled' ) {
			if ( !empty( $data["invoices"] ) ) {
				$pdf = Mage::getModel( 'sales/order_pdf_invoice' )->getPdf( $data["invoices"] );
				$pdfname = 'invoice';
			}
			$pdffile = $pdf->render();
			$mail->getMail()->createAttachment( $pdffile,
				'application/pdf',
				Zend_Mime::DISPOSITION_ATTACHMENT,
				Zend_Mime::ENCODING_BASE64,
				$pdfname.'.pdf'
			);
		}
		else {
			$mail->getMail();
		}

		// Write the log
		Mage::log( '' );
		Mage::log( '[kinento-invoice-reminder] Sending a reminder' );
		Mage::log( '[kinento-invoice-reminder] To: '.$emailaddress );
		Mage::log( '[kinento-invoice-reminder] From: '.Mage::getStoreConfig( self::XML_PATH_EMAIL_SENDER, $invoice->getStoreId() ));
		Mage::log( '[kinento-invoice-reminder] Template: '.$template );
		Mage::log( '[kinento-invoice-reminder] StoreID: '.$invoice->getStoreId() );

		// Send out the actual email
		$mail->setDesignConfig( array( 'area' => 'frontend', 'store' => $invoice->getStoreId() ) )
		->sendTransactional(
			$template,
			Mage::getStoreConfig( self::XML_PATH_EMAIL_SENDER, $invoice->getStoreId() ),
			$emailaddress,
			null,
			$data
		);

		// Give feedback to the user
		Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'invoicereminder' )->__( 'Send email to %s for invoice %d', $emailaddress, $invoice->getIncrementId() ) );

		// Finalize
		$translate->setTranslateInline( true );
	}

}

?>

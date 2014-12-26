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


class Kinento_Invoicereminder_ViewController extends Mage_Adminhtml_Controller_Action {

	public function indexAction() {

		$this->loadLayout();
		$this->_setActiveMenu( 'invoicereminder/view' );

		$this->getLayout()
		->getBlock( 'content' )->append(
			$this->getLayout()->createBlock( 'invoicereminder/view_main' )
		);
		$this->renderLayout();
	}

	public function changeAction() {
		$id = $this->getRequest()->getParam( 'id', false );
		$status = $this->getRequest()->getParam( 'status', false );

		try {
			$invoices = Mage::getModel( 'invoicereminder/invoicereminder' )->getCollection()->addFieldToFilter( 'increment_id', $id )->getItems();
			foreach ( $invoices as $invoice ) {
				$invoice->setStatus( $status );
				$invoice->save();
				Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'invoicereminder' )->__( 'Status changed.' ) );
			}
		} catch ( Exception $e ) {
			Mage::getSingleton( 'adminhtml/session' )->addError( $e->getMessage() );
		}

		$this->_redirectReferer();
	}

	public function manualAction() {
		$id = $this->getRequest()->getParam( 'id', false );

		try {
			Mage::getModel( 'invoicereminder/sender' )->manualMail( $id );
			Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'invoicereminder' )->__( '1 reminder sent manually' ) );

		} catch ( Exception $e ) {
			Mage::getSingleton( 'adminhtml/session' )->addError( $e->getMessage() );
		}

		$this->_redirectReferer();
	}

	public function manipulateAction() {
		$id = $this->getRequest()->getParam( 'id', false );
		$option = $this->getRequest()->getParam( 'option', false );

		try {
			$invoices = Mage::getModel( 'invoicereminder/invoicereminder' )->getCollection()->addFieldToFilter( 'increment_id', $id )->getItems();
			foreach ( $invoices as $invoice ) {
				if ( $option == 'add' ) {
					$invoice->setInvoicereminders( $invoice->getInvoicereminders()+1 );
				}
				if ( $option == 'sub' ) {
					$invoice->setInvoicereminders( $invoice->getInvoicereminders()-1 );
				}
				if ( $option == 'reset' ) {
					$invoice->setInvoicereminders( 0 );
				}
				$invoice->save();
			}
		} catch ( Exception $e ) {
			Mage::getSingleton( 'adminhtml/session' )->addError( $e->getMessage() );
		}

		$this->_redirectReferer();
	}

	public function massChangeAction() {
		$ids = $this->getRequest()->getParam( 'invoicereminder' );
		$status = $this->getRequest()->getParam( 'status', false );

		if ( !is_array( $ids ) ) {
			Mage::getSingleton( 'adminhtml/session' )->addError( Mage::helper( 'invoicereminder' )->__( 'Please select one or more orders' ) );
		} else {
			try {
				foreach ( $ids as $id ) {
					$invoiceid = Mage::getModel( 'sales/order' )->load( $id )->getIncrementId();
					$invoices = Mage::getModel( 'invoicereminder/invoicereminder' )->getCollection()->addFieldToFilter( 'increment_id', $invoiceid )->getItems();
					foreach ( $invoices as $invoice ) {
						$invoice->setStatus( $status );
						$invoice->save();
					}
				}
				Mage::getSingleton( 'adminhtml/session' )->addSuccess(
					Mage::helper( 'invoicereminder' )->__( 'Total of %d order(s) were successfully updated', count( $ids ) )
				);
			} catch ( Exception $e ) {
				Mage::getSingleton( 'adminhtml/session' )->addError( $e->getMessage() );
			}
		}
		$this->_redirect( '*/*/index' );
	}

	// Function to send out reminders now, independent of the settings. Sends out multiple emails (massAction)
	public function massSendAction() {
		$ids = $this->getRequest()->getParam( 'invoicereminder' );
		if ( !is_array( $ids ) ) {
			Mage::getSingleton( 'adminhtml/session' )->addError( Mage::helper( 'invoicereminder' )->__( 'Please select one or more orders' ) );
		} else {
			try {
				foreach ( $ids as $id ) {
					$invoiceid = Mage::getModel( 'sales/order' )->load( $id )->getIncrementId();
					Mage::getModel( 'invoicereminder/sender' )->manualMail( $invoiceid );
				}
				Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'invoicereminder' )->__( '%d reminder(s) sent manually', count( $ids ) ) );

			} catch ( Exception $e ) {
				Mage::getSingleton( 'adminhtml/session' )->addError( $e->getMessage() );
			}
		}
		$this->_redirect( '*/*/index' );
	}

}

?>

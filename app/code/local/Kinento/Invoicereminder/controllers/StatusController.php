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


class Kinento_Invoicereminder_Statuscontroller extends Mage_Adminhtml_Controller_Action {

	// Set multiple invoices to status 'paid'
	public function invoicespaidAction() {
		$count = 0;

		# Get all the invoice IDs from the selection
		$invoice_ids = $this->getRequest()->getPost( 'invoice_ids' );

		# Iterate over all invoices and set the as 'paid'
		foreach ($invoice_ids as $invoice_id) {
			$invoice = Mage::getModel( 'sales/order_invoice' )->load( $invoice_id );
			$invoice->setState( Mage_Sales_Model_Order_Invoice::STATE_PAID );
			$invoice->save();
			$count += 1;
		}

		// Output the total number of changed invoice statusses
		Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'invoicereminder' )->__( '%d invoice(s) set as paid', $count ) );
		$this->getResponse()->setRedirect( $this->getUrl( 'adminhtml/sales_invoice/index' ) );

	}

	// Set a single invoice to status 'paid'
	public function invoicepaidAction() {

		# Get the invoice ID
		$invoice_id = $this->getRequest()->getParam( 'invoice_id' );

		// Set the invoice state to 'paid'
		$invoice = Mage::getModel( 'sales/order_invoice' )->load( $invoice_id );
		$invoice->setState( Kinento_Invoicereminder_Model_Order_Invoice::STATE_PAID );
		$invoice->save();

		// Output a message to confirm the changed invoice status
		Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'invoicereminder' )->__( 'Invoice set as paid' ) );
		$this->getResponse()->setRedirect( $this->getUrl( 'adminhtml/sales_invoice/index' ) );
	}

	// Set multiple invoices to status 'pending payment'
	public function invoicesunpaidAction() {
		$count = 0;

		# Get all the invoice IDs from the selection
		$invoice_ids = $this->getRequest()->getPost( 'invoice_ids' );

		# Iterate over all invoices and set the as 'pending payment'
		foreach ($invoice_ids as $invoice_id) {
			$invoice = Mage::getModel( 'sales/order_invoice' )->load( $invoice_id );
			$invoice->setState( Kinento_Invoicereminder_Model_Order_Invoice::STATE_PENDING_PAYMENT );
			$invoice->save();
			$count += 1;
		}

		// Output the total number of changed invoice statusses
		Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'invoicereminder' )->__( '%d invoice(s) set as pending payment', $count ) );
		$this->getResponse()->setRedirect( $this->getUrl( 'adminhtml/sales_invoice/index' ) );

	}

	// Set a single invoice to status 'pending payment'
	public function invoiceunpaidAction() {

		# Get the invoice ID
		$invoice_id = $this->getRequest()->getParam( 'invoice_id' );

		// Set the invoice state to 'pending payment'
		$invoice = Mage::getModel( 'sales/order_invoice' )->load( $invoice_id );
		$invoice->setState( Kinento_Invoicereminder_Model_Order_Invoice::STATE_PENDING_PAYMENT );
		$invoice->save();

		// Output a message to confirm the changed invoice status
		Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'invoicereminder' )->__( 'Invoice set as pending payment' ) );
		$this->getResponse()->setRedirect( $this->getUrl( 'adminhtml/sales_invoice/index' ) );
	}

}

?>

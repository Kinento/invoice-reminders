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


class Kinento_Invoicereminder_SendController extends Mage_Adminhtml_Controller_Action {

	public function indexAction() {

		// Calculate the total of send items before sending
		$count1 = 0;
		$invoices = Mage::getModel( 'invoicereminder/invoicereminder' )->getCollection()->getItems();
		foreach ( $invoices as $invoice ) {
			$count1 = $count1 + $invoice->getInvoicereminders();
		}

		// Send the reminders
		Mage::getModel( 'invoicereminder/sender' )->prepareMail();

		// Calculate the total of send items after sending
		$count2 = 0;
		$invoices = Mage::getModel( 'invoicereminder/invoicereminder' )->getCollection()->getItems();
		foreach ( $invoices as $invoice ) {
			$count2 = $count2 + $invoice->getInvoicereminders();
		}

		// Output the total number of sent reminders
		Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'invoicereminder' )->__( '%d reminder(s) sent', ( $count2 - $count1 ) ) );
		$this->getResponse()->setRedirect( $this->getUrl( '*/view/' ) );

	}

}

?>

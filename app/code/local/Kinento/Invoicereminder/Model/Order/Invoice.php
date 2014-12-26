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


class Kinento_Invoicereminder_Model_Order_Invoice extends Mage_Sales_Model_Order_Invoice {

	// Add a new status ('Pending Payment')
	const STATE_PENDING_PAYMENT = 4;

	// Replace the original 'getStates' function in 'app/code/core/Mage/Sales/Model/Order/invoice.php'
	public static function getStates() {
		if ( is_null( self::$_states ) ) {

			// Add a new status (pending payment)
			self::$_states = array(
				self::STATE_OPEN            => Mage::helper( 'sales' )->__( 'Pending' ),
				self::STATE_PAID            => Mage::helper( 'sales' )->__( 'Paid' ),
				self::STATE_CANCELED        => Mage::helper( 'sales' )->__( 'Canceled' ),
				self::STATE_PENDING_PAYMENT => Mage::helper( 'sales' )->__( 'Pending Payment' ),
			);
		}
		return self::$_states;
	}

	// Replace the original 'pay' function in 'app/code/core/Mage/Sales/Model/Order/invoice.php'
	public function pay() {
		if ( $this->_wasPayCalled ) {
			return $this;
		}
		$this->_wasPayCalled = true;

		// Change the default state to 'pending payment'
		$invoiceState = self::STATE_PENDING_PAYMENT;
		if ( $this->getOrder()->getPayment()->hasForcedState() ) {
			$invoiceState = $this->getOrder()->getPayment()->getForcedState();
		}

		$this->setState( $invoiceState );

		$this->getOrder()->getPayment()->pay( $this );
		$this->getOrder()->setTotalPaid(
			$this->getOrder()->getTotalPaid()+$this->getGrandTotal()
		);
		$this->getOrder()->setBaseTotalPaid(
			$this->getOrder()->getBaseTotalPaid()+$this->getBaseGrandTotal()
		);
		Mage::dispatchEvent( 'sales_order_invoice_pay', array( $this->_eventObject=>$this ) );
		return $this;
	}

}

?>

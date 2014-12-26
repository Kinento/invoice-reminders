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


class Kinento_Invoicereminder_Block_View_Main_Widget_Column_Renderer_Payment extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

	public function render( Varien_Object $row ) {
		$html = '';
		$order = Mage::getModel( 'sales/order' )->loadByIncrementId( $row->getOrderIncrementId() );
		if ( in_array( $order->getPayment()->getMethod(), explode( ',', Mage::getStoreConfig( 'invoicereminder/generalsettings/invoicepayments' ) ) ) ) {
			$html .= 'Enabled (';
			$html .= $order->getPayment()->getMethod();
			$html .= ')';
		}
		else {
			$html .= 'Disabled (';
			$html .= $order->getPayment()->getMethod();
			$html .= ')';
		}
		return $html;
	}

}

?>


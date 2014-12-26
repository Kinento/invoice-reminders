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


class Kinento_Invoicereminder_Model_Mysql4_Invoicereminder_Collection extends Varien_Data_Collection_Db {

	protected $_bookTable;

	public function __construct() {
		$resources = Mage::getSingleton( 'core/resource' );
		parent::__construct( $resources->getConnection( 'invoicereminder_read' ) );
		$this->_invoicereminderTable = $resources->getTableName( 'invoicereminder/invoicereminder' );

		$this->_select->from(
			array( 'invoicereminder'=>$this->_invoicereminderTable ),
			array( '*' )
		);
		$this->setItemObjectClass( Mage::getConfig()->getModelClassName( 'invoicereminder/invoicereminder' ) );
	}

}

?>

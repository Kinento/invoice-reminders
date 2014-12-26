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


class Kinento_Invoicereminder_Block_Sales_Invoice_Grid extends Mage_Adminhtml_Block_Sales_Invoice_Grid {

	// Replace the original '_prepareMassaction' function in 'app/code/core/Mage/Adminhtml/Block/Sales/Invoice/Grid.php'
	protected function _prepareMassaction() {
		$this->setMassactionIdField( 'entity_id' );
		$this->getMassactionBlock()->setFormFieldName( 'invoice_ids' );
		$this->getMassactionBlock()->setUseSelectAll( false );

		$this->getMassactionBlock()->addItem( 'pdfinvoices_order', array(
				'label'=> Mage::helper( 'sales' )->__( 'PDF Invoices' ),
				'url'  => $this->getUrl( '*/sales_invoice/pdfinvoices' ),
			) );

		// Add a new mass-action (mark as paid)
		$this->getMassactionBlock()->addItem( 'invoicespaid', array(
				'label'=> Mage::helper( 'sales' )->__( 'Mark as paid' ),
				'url'  => $this->getUrl( 'invoicereminder/status/invoicespaid' ),
			) );

		// Add a new mass-action (mark as pending payment)
		$this->getMassactionBlock()->addItem( 'invoicesunpaid', array(
				'label'=> Mage::helper( 'sales' )->__( 'Mark as pending payment' ),
				'url'  => $this->getUrl( 'invoicereminder/status/invoicesunpaid' ),
			) );

		return $this;
	}

	// Replace the original '_prepareColumns' function in 'app/code/core/Mage/Adminhtml/Block/Sales/Invoice/Grid.php'
	protected function _prepareColumns() {
		$this->addColumn( 'increment_id', array(
				'header'    => Mage::helper( 'sales' )->__( 'Invoice #' ),
				'index'     => 'increment_id',
				'type'      => 'text',
			) );

		$this->addColumn( 'created_at', array(
				'header'    => Mage::helper( 'sales' )->__( 'Invoice Date' ),
				'index'     => 'created_at',
				'type'      => 'datetime',
			) );

		$this->addColumn( 'order_increment_id', array(
				'header'    => Mage::helper( 'sales' )->__( 'Order #' ),
				'index'     => 'order_increment_id',
				'type'      => 'text',
			) );

		$this->addColumn( 'order_created_at', array(
				'header'    => Mage::helper( 'sales' )->__( 'Order Date' ),
				'index'     => 'order_created_at',
				'type'      => 'datetime',
			) );

		$this->addColumn( 'billing_name', array(
				'header' => Mage::helper( 'sales' )->__( 'Bill to Name' ),
				'index' => 'billing_name',
			) );

		$this->addColumn( 'state', array(
				'header'    => Mage::helper( 'sales' )->__( 'Status' ),
				'index'     => 'state',
				'type'      => 'options',
				'options'   => Mage::getModel( 'sales/order_invoice' )->getStates(),
			) );

		$this->addColumn( 'grand_total', array(
				'header'    => Mage::helper( 'customer' )->__( 'Amount' ),
				'index'     => 'grand_total',
				'type'      => 'currency',
				'align'     => 'right',
				'currency'  => 'order_currency_code',
			) );

		$this->addColumn( 'action',
			array(
				'header'    => Mage::helper( 'sales' )->__( 'Action' ),
				'width'     => '50px',
				'type'      => 'action',
				'getter'     => 'getId',
				'actions'   => array(
					array(
						'caption' => Mage::helper( 'sales' )->__( 'View' ),
						'url'     => array( 'base'=>'*/sales_invoice/view' ),
						'field'   => 'invoice_id'
					)
				),
				'filter'    => false,
				'sortable'  => false,
				'is_system' => true
			) );

		// Add a new action column (mark as paid)
		$this->addColumn( 'action_paid',
			array(
				'header'    => Mage::helper( 'sales' )->__( 'Action' ),
				'width'     => '50px',
				'type'      => 'action',
				'getter'     => 'getId',
				'actions'   => array(
					array(
						'caption' => Mage::helper( 'sales' )->__( 'Mark as paid' ),
						'url'     => array( 'base'=>'invoicereminder/status/invoicepaid' ),
						'field'   => 'invoice_id'
					)
				),
				'filter'    => false,
				'sortable'  => false,
				'is_system' => true
			) );

		// Add a new action column (mark as pending payment)
		$this->addColumn( 'action_unpaid',
			array(
				'header'    => Mage::helper( 'sales' )->__( 'Action' ),
				'width'     => '50px',
				'type'      => 'action',
				'getter'     => 'getId',
				'actions'   => array(
					array(
						'caption' => Mage::helper( 'sales' )->__( 'Mark as pending payment' ),
						'url'     => array( 'base'=>'invoicereminder/status/invoiceunpaid' ),
						'field'   => 'invoice_id'
					)
				),
				'filter'    => false,
				'sortable'  => false,
				'is_system' => true
			) );

		$this->addExportType( '*/*/exportCsv', Mage::helper( 'sales' )->__( 'CSV' ) );
		$this->addExportType( '*/*/exportExcel', Mage::helper( 'sales' )->__( 'Excel XML' ) );

		return parent::_prepareColumns();
	}

}

?>

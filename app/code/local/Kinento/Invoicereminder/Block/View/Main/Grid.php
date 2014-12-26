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


class Kinento_Invoicereminder_Block_View_Main_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId( 'reminderGrid' );
		$this->setDefaultSort( 'entity_id' );
	}

	protected function _prepareCollection() {
		$history = new Zend_Date( Mage::getStoreConfig( 'invoicereminder/generalsettings/startingdate' ) );
		$now = new Zend_Date( Mage::getModel( 'core/date' )->gmtTimestamp() );
		$statusfilter = explode( ',', Mage::getStoreConfig( 'invoicereminder/generalsettings/invoicestatuses' ) );

		// Get the data from the database
		$collection = Mage::getResourceModel( 'sales/order_invoice_grid_collection' )
		->addAttributeToSelect( '*' )
		->addAttributeToFilter( 'state', array( 'in' => $statusfilter ) )
		->addAttributeToFilter( 'created_at', array( 'from' => $history, 'to' => $now, 'datetime'=>true ) );
		$this->setCollection( $collection );

		// Create new entry for those that do not exist
		$invoiceremindermodel = Mage::getModel( 'invoicereminder/invoicereminder' );
		$invoices = $collection->getItems();

		foreach ( $invoices as $invoice ) {
			$reminderinvoices = $invoiceremindermodel->getCollection()->addFieldToFilter( 'increment_id', $invoice->getIncrementId() )->getItems();
			if ( empty( $reminderinvoices ) ) {
				$data = array(
					"increment_id"  => $invoice->getIncrementId(),
					"invoicereminders"  => 0,
				);
				$invoiceremindermodel->setData( $data );
				$invoiceremindermodel->save();
			}
		}

		// Get the data from the database
		$collection = Mage::getResourceModel( 'sales/order_invoice_grid_collection' )
		->addAttributeToSelect( '*' )
		->addAttributeToFilter( 'state', array( 'in' => $statusfilter ) )
		->addAttributeToFilter( 'created_at', array( 'from' => $history, 'to' => $now, 'datetime'=>true ) );
		$this->setCollection( $collection );
		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {

		$this->addColumn( 'order_increment_id', array(
				'header'=> Mage::helper( 'invoicereminder' )->__( 'Order id' ),
				'width' => '100px',
				'type'  => 'text',
				'index' => 'order_increment_id',
			) );

		$this->addColumn( 'increment_id', array(
				'header'=> Mage::helper( 'invoicereminder' )->__( 'Invoice id' ),
				'width' => '100px',
				'type'  => 'text',
				'index' => 'increment_id',
			) );

		if ( !Mage::app()->isSingleStoreMode() ) {
			$this->addColumn( 'store_id', array(
					'header'    => Mage::helper( 'invoicereminder' )->__( 'Store' ),
					'index'     => 'store_id',
					'type'      => 'store',
					'store_view'=> true,
					'display_deleted' => true,
					'width' => '200px',
				) );
		}

		//$this->addColumn( 'order_created_at', array(
		//		'header' => Mage::helper( 'invoicereminder' )->__( 'Order date' ),
		//		'index' => 'order_created_at',
		//		'type' => 'datetime',
		//		'width' => '100px',
		//	) );

		$this->addColumn( 'invoice_created_at', array(
				'header' => Mage::helper( 'invoicereminder' )->__( 'Invoice date' ),
				'index' => 'created_at',
				'type' => 'datetime',
				'width' => '100px',
			) );

		$this->addColumn( 'billing_name', array(
				'header' => Mage::helper( 'invoicereminder' )->__( 'Billing name' ),
				'index' => 'billing_name',
				'width' => '200px',
			) );

		$this->addColumn( 'grand_total', array(
				'header' => Mage::helper( 'invoicereminder' )->__( 'Amount' ),
				'index' => 'grand_total',
				'type'  => 'currency',
				'currency' => 'order_currency_code',
				'width' => '100px',
			) );

		$this->addColumn( 'invoicestate', array(
				'header'  => Mage::helper( 'invoicereminder' )->__( 'Invoice state' ),
				'index'   => 'state',
				'type'    => 'options',
				'options' => Mage::getModel( 'sales/order_invoice' )->getStates(),
				'width'   => '70px',
			) );

		// Start of widgets

		// Widget 1
		$this->addColumn( 'ordergroup', array(
				'header' => Mage::helper( 'invoicereminder' )->__( 'Invoice type' ),
				'renderer' => 'Kinento_Invoicereminder_Block_View_Main_Widget_Column_Renderer_Grouptype',
				'type'  => 'text',
				'width' => '70px',
				'filter' => false,
				'sortable' => false,
			) );

		// Widget 2
		$this->addColumn( 'reminders', array(
				'header' => Mage::helper( 'invoicereminder' )->__( 'Reminders sent' ),
				'renderer' => 'Kinento_Invoicereminder_Block_View_Main_Widget_Column_Renderer_Reminderssent',
				'type'  => 'text',
				'width' => '70px',
				'filter' => false,
				'sortable' => false,
			) );

		// Widget 3
		$this->addColumn( 'emailstatus', array(
				'header' => Mage::helper( 'invoicereminder' )->__( 'Disable notifications' ),
				'renderer' => 'Kinento_Invoicereminder_Block_View_Main_Widget_Column_Renderer_Status',
				'type'  => 'text',
				'width' => '70px',
				'filter' => false,
				'sortable' => false,
			) );

		// Widget 4
		$this->addColumn( 'paymentmethod', array(
				'header' => Mage::helper( 'invoicereminder' )->__( 'Payment method' ),
				'renderer' => 'Kinento_Invoicereminder_Block_View_Main_Widget_Column_Renderer_Payment',
				'type'  => 'text',
				'width' => '70px',
				'filter' => false,
				'sortable' => false,
			) );

		// Widget 5
		$this->addColumn( 'manualreminder', array(
				'header' => Mage::helper( 'invoicereminder' )->__( 'Manual reminders' ),
				'renderer' => 'Kinento_Invoicereminder_Block_View_Main_Widget_Column_Renderer_Manual',
				'type'  => 'text',
				'width' => '70px',
				'filter' => false,
				'sortable' => false,
			) );

		// End of widgets

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction() {
		$this->setMassactionIdField( 'entity_id' );
		$this->getMassactionBlock()->setFormFieldName( 'invoicereminder' );

		$this->getMassactionBlock()->addItem( 'send_reminder_now', array(
				'label'    => Mage::helper( 'invoicereminder' )->__( 'Force send reminder(s) now' ),
				'url'      => $this->getUrl( '*/view/massSend' )
			) );

		$this->getMassactionBlock()->addItem( 'selected_enable', array(
				'label'    => Mage::helper( 'invoicereminder' )->__( 'Enable selected' ),
				'url'      => $this->getUrl( '*/view/massChange/status/enabled' )
			) );

		$this->getMassactionBlock()->addItem( 'selected_disable', array(
				'label'    => Mage::helper( 'invoicereminder' )->__( 'Disable selected' ),
				'url'      => $this->getUrl( '*/view/massChange/status/disabled' )
			) );

		return $this;
	}

}

?>

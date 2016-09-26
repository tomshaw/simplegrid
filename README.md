# SimpleGrid - Zend Framework 2 Grid Controller Plugin

SimpleGrid is a Zend Framework 2 controller plugin that makes it extremely easy and flexible to setup a custom grid in your application. SimpleGrid doesn't create a grid for you it just makes it much easier to develop one. SimpleGrid uses sessions and unique namespaces to remember sorting and search criteria and to uniquely distinguish grids developed on your system. Any input or pull requests would be greatly appreciated.

## Installation

  Simply enable the module inside your application.config.php

    return array(
        'modules' => array(
            'Application',
            'SimpleGrid'
        ),
	
## Implementation Notes 

Implementation of the module is quite simple.

  Create a route that your grid uses.

    'cart-user' => array(
        'type' => 'Segment',
        'options' => array(
            'route' => '/admin/user[/order/:order/sort/:sort/page/:page]',
            'constraints' => array(
                'page' => '[0-9]+',
                'sort' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'order' => '[a-zA-Z][a-zA-Z0-9_-]*'
            ),
            'defaults' => array(
                'controller' => 'Admin\Controller\User',
                'action' => 'index',
                'order' => 'id',
                'sort' => 'asc',
                'page' => 1
			)
        )
    )

  Implement the helper in your action controller. Please note the first and only grid parameter should be unique and not clash with other grids on your system. Below I'm using an Admin module, User controller and Index action. Namespaces do not have to follow this convention but should be unique none the less.

    class UserController extends AbstractActionController
    {
        protected $userTable;

        public function getUserTable()
        {
            if (!$this->userTable) {
                $sm = $this->getServiceLocator();
                $this->userTable = $sm->get('Application\Model\UserTable');
            }
            return $this->userTable;
        }

        public function indexAction()
        {
            $grid = $this->grid('Admin\User\Index');

            $select = $this->getUserTable()->fetchAll($grid);

            $adapter = new DbSelect($select, $this->getUserTable()->getAdapter());

            $paginator = new Paginator($adapter);

            $paginator->setCurrentPageNumber($grid['page']);

            $paginator->setItemCountPerPage(20);

            return new ViewModel(array_merge($grid, array('paginator'=>$paginator)));
        }
    }
	
  Create a method in your model that utilizes the grid array.
  
    public function fetchAll($data = array())
    {
        $sql = new Sql($this->tableGateway->getAdapter());
    
        $select = $sql->select()
            ->from(array('user' => 'user'))
            ->columns(array('*'));

        if (isset($data['name']) && !empty($data['name'])) {
            $select->where->like('user.name', '%'.strtolower($data['name']).'%');
        }

        if (isset($data['newsletter']) && !empty($data['newsletter']) && $data['newsletter'] != '-1') {
            $select->where->equalTo('user.newsletter', intval($data['newsletter']-1));
        }

        $select->order(array($data['order'] . ' ' . strtoupper($data['sort'])));
        
        return $select;
    }
  
  Code your template like you normally would.

    <div class="page-header">
      <h1>Users<small> Browse and manage system users.</small></h1>
    </div>

    <div>
      <?php echo $this->paginationControl($this->paginator, 'Jumping', 'pagination', array('route' => 'cart-user')); ?>
    </div>

    <form method="post" action="/admin/user">

      <div class="input-prepend input-append command-buttons pull-right">
        <button id="submit" data-loading-text="loading..." class="btn"><i class="icon-refresh" style="margin-top:1px;"></i> Filter Results</button>
        <button id="grid-reset" class="btn dropdown-toggle"><span class="icon-retweet"></span></button>
      </div>
	  
	  <?php $switch = ($this->sort == 'desc') ? 'asc' : 'desc' ?>

      <table class="table table-striped table-bordered table-condensed">
        <thead>
          <tr>
            <th class="sorting" style="width: 5%; text-align: center;"><a href="/admin/user/order/id/sort/<?php echo $switch ?>/page/<?php echo $this->page ?>">ID</a></th>
            <th class="sorting" style="width: 20%; text-align: left;"><a href="/admin/user/order/name/sort/<?php echo $switch ?>/page/<?php echo $this->page ?>">Customer Name</a></th>
            <th class="sorting" style="width: 20%; text-align: left;"><a href="/admin/user/order/email/sort/<?php echo $switch ?>/page/<?php echo $this->page ?>">Email</a></th>
            <th class="sorting" style="width: 30%; text-align: left;"><a href="/admin/user/order/address/sort/<?php echo $switch ?>/page/<?php echo $this->page ?>">Address</a></th>
            <th class="sorting" style="width: 10%; text-align: left;"><a href="/admin/user/order/phone/sort/<?php echo $switch ?>/page/<?php echo $this->page ?>">Phone</a></th>
            <th class="sorting" style="width: 5%; text-align: left;"><a href="/admin/user/order/newsletter/sort/<?php echo $switch ?>/page/<?php echo $this->page ?>">Newsletter</a></th>
          </tr>
        </thead>
        <tbody>
          <tr class="grid-inputs">
            <td style="text-align:center;"><input type="text" name="id" value="<?php echo $this->escapeHtml($this->id) ?>" style="width:50px;"></td>
            <td style="text-align:left;"><input type="text" name="name" value="<?php echo $this->escapeHtml($this->name) ?>"></td>
            <td style="text-align:left;"><input type="text" name="email" value="<?php echo $this->escapeHtml($this->email) ?>"></td>
            <td style="text-align:left;"><input type="text" name="address" value="<?php echo $this->escapeHtml($this->address) ?>"></td>
            <td style="text-align:left;"><input type="text" name="phone" value="<?php echo $this->escapeHtml($this->phone) ?>"></td>
            <td style="text-align:left;">
              <select name="newsletter">
                <?php foreach(array_merge(array('-1'=>''), array('0'=>'Not Subscribed','1'=>'Subscribed')) as $key => $value): ?>
                <?php $selected = ($this->newsletter == $key) ? 'selected="selected"': '' ?>
                <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value; ?></option>
                <?php endforeach; ?>
              </select>
            </td>
          </tr>
          <?php if (count($this->paginator)): ?>
          <?php foreach ($this->paginator as $row): ?>
          <tr style="cursor:pointer;" title="/admin/user/view/id/<?php echo $this->escapeHtml($row['id']) ?>" data-href="/admin/user/view/id/<?php echo $this->escapeHtml($row['id']) ?>">
            <td style="text-align:center;"><?php echo $this->escapeHtml($row['id']) ?></td>
            <td style="text-align:left;"><a href="/admin/user/edit/id/<?php echo $this->escapeHtml($row['id']) ?>" title="Edit user: <?php echo $this->escapeHtml($row['name']); ?>"><?php echo $this->escapeHtml($row['name']); ?></a></td>
            <td style="text-align:center;"><?php echo $this->escapeHtml($row['email']) ?></td>
            <td style="text-align:left;"><?php echo $this->escapeHtml($row['address1']) ?> <?php echo $this->escapeHtml($row['address2']) ?> <?php echo $this->escapeHtml($row['city']) ?> <?php echo $this->escapeHtml($row['state']) ?> <?php echo $this->escapeHtml($row['zipcode']) ?></td>
            <td style="text-align:center;"><?php echo $this->escapeHtml($row['phone']) ?></td>
            <td style="text-align:center;"><?php if($row['newsletter'] == 1): ?>Subscribed<?php else: ?>Not Subscribed<?php endif; ?></td>
          </tr>
          <?php endforeach ?>
          <?php endif; ?>
        </tbody>
      </table>
    </form>
    <script>
    $('#grid-reset').click(function(e) {
        $(this).closest('form').find('input[type=text], select').val('').submit();
    });
    $("input#created").datepicker({dateFormat: 'yy-mm-dd'});
    $("input#updated").datepicker({dateFormat: 'yy-mm-dd'});
    </script>

## Styling Tips

  To get your inputs to play nicely inside the table cells, you need to tweak the margins a little. It's also a good idea to prevent those cells from wrapping.

    table tbody tr td input {
	    margin: 2px 2px;
	    width: 90%;
    }

    table input[type="text"] {
	    margin-bottom: 1px;
    }

    table thead tr a {
	    margin-left: 2px;
    }

    tr.grid-inputs td {
	    white-space: nowrap;
	    text-align: left;
	    vertical-align: top;
    }  

## License

#### (The MIT License)

Copyright &copy; 2013 [tom@visfx.me](mailto:tom@visfx.me) 

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the 'Software'), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

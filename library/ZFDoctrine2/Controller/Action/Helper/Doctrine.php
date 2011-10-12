<?php

namespace ZFDoctrine2\Controller\Action\Helper;

class Doctrine
	extends \Zend_Controller_Action_Helper_Abstract
{

	/**
	 * @var ZFDoctrine2\Application\Resource\Doctrine Doctrine resource.
	 */
	protected $_resource;

	/**
	 * Retrieve DBAL Connection based on its name. If no argument is provided,
	 * it will attempt to get the default Connection.
	 * If DBAL Connection name could not be found, NameNotFoundException is thrown.
	 *
	 * @throws ZFDoctrine2\Exception\NameNotFoundException
	 *
	 * @param string $connName Optional DBAL Connection name
	 *
	 * @return Doctrine\DBAL\Connection DBAL Connection
	 */
	public function getConnection($connName = null)
	{
		return $this->_getResource()->getConnection($connName);
	}

	/**
	 * Retrieves a list of names for all configured Connections
	 *
	 * @return array
	 */
	public function getConnectionNames()
	{
		return $this->_getResource()->getConnectionNames();
	}

	/**
	 * Retrieve Cache Instance based on its name. If no argument is provided,
	 * it will attempt to get the default Instance.
	 * If Cache Instance name could not be found, NameNotFoundException is thrown.
	 *
	 * @throws ZFDoctrine2\Exception\NameNotFoundException
	 *
	 * @param string $cacheName Optional Cache Instance name
	 *
	 * @return Doctrine\Common\Cache\Cache Cache Instance
	 */
	public function getCacheInstance($cacheName = null)
	{
		return $this->_getResource()->getCacheInstance($cacheName);
	}

	/**
	 * Retrieves a list of names for all cache instances configured
	 *
	 * @return array
	 */
	public function getCacheInstanceNames()
	{
		return $this->_getResource()->getCacheInstanceNames();
	}

	/**
	 * Retrieve ORM EntityManager based on its name. If no argument provided,
	 * it will attempt to get the default EntityManager.
	 * If ORM EntityManager name could not be found, NameNotFoundException is thrown.
	 *
	 * @throws ZFDoctrine2\Exception\NameNotFoundException
	 *
	 * @param string $emName Optional ORM EntityManager name
	 *
	 * @return Doctrine\ORM\EntityManager ORM EntityManager
	 */
	public function getEntityManager($emName = null)
	{
		return $this->_getResource()->getEntityManager($emName);
	}

	/**
	 * Retrieves a list of names for all Entity Managers configured and/or loaded
	 *
	 * @return array
	 */
	public function getEntityManagerNames()
	{
		return $this->_getResource()->getEntityManagerNames();
	}

	/**
	 * Retrieves the Doctrine resource from the action controller.
	 *
	 * @return ZFDoctrine2\Application\Resource\Doctrine
	 */
	protected function _getResource()
	{
		if (!$this->_resource) {
			$controller = $this->getActionController();
			$bootstrap = $controller->getInvokeArg('bootstrap');
			$this->_resource = $bootstrap->getResource('doctrine');
		}

		return $this->_resource;
	}

}


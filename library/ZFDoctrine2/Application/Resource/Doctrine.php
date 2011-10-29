<?php

namespace ZFDoctrine2\Application\Resource;

use ZFDoctrine2\Exception;
use Doctrine\DBAL\Types\Type;
use Doctrine\Common\Annotations\AnnotationRegistry;

/**
 * Zend Application Resource Doctrine class
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class Doctrine extends \Zend_Application_Resource_ResourceAbstract
{

	/**
	 * @var string Default DBAL Connection name.
	 */
	protected $_defaultConnection = 'default';

	/**
	 * @var default Default Cache Instance name.
	 */
	protected $_defaultCacheInstance = 'default';

	/**
	 * @var string Default ORM EntityManager name.
	 */
	protected $_defaultEntityManager = 'default';

	/**
	 * @var array Doctrine DBAL configuration.
	 */
	protected $_dbalConfiguration = array();

	/**
	 * @var array Doctrine Cache configuration.
	 */
	protected $_cacheConfiguration = array();

	/**
	 * @var array Doctrine ORM configuration.
	 */
	protected $_ormConfiguration = array();

	/**
	 * @var array Available DBAL Connections.
	 */
	protected $_connections = array();

	/**
	 * @var array Available Cache Instances.
	 */
	protected $_cacheInstances = array();

	/**
	 * @var array Available ORM EntityManagers.
	 */
	protected $_entityManagers = array();

	/**
	 * Initializes the Doctrine resource.
	 *
	 * @return ZFDoctrine2\Doctrine\Container
	 */
	public function init()
	{
		return $this->getEntityManager();
	}

	/**
	 * Prepare Doctrine Cache configuration.
	 *
	 * @param array $options Doctrine Cache configuration.
	 */
	public function setCache(array $options)
	{
		if (isset($options['defaultCacheInstance'])) {
			$this->_defaultCacheInstance = $options['defaultCacheInstance'];
			unset($options['defaultCacheInstance']);
		}

		$cacheTemplate = array(
			'adapterClass' => 'Doctrine\Common\Cache\ArrayCache',
			'namespace' => '',
			'options' => array()
		);

		$instances = array();
		foreach ($options as $name => $instance) {
			$instances[$name] = array_replace_recursive($cacheTemplate, $instance);
		}

		$this->_cacheConfiguration = $instances;
	}

	/**
	 * Prepare DBAL Connection configuration.
	 *
	 * @param array $options Doctrine DBAL configuration.
	 */
	public function setDbal(array $options)
	{
		if (isset($options['defaultConnection'])) {
			$this->_defaultConnection = $options['defaultConnection'];
			unset($options['defaultConnection']);
		}

		$connectionTemplate = array(
			'eventManagerClass' => 'Doctrine\Common\EventManager',
			'eventSubscribers' => array(),
			'configurationClass' => 'Doctrine\DBAL\Configuration',
			'sqlLoggerClass' => null,
			'types' => array(),
			'parameters' => array(
				'wrapperClass' => null,
				'driver' => 'pdo_mysql',
				'host' => 'localhost',
				'user' => 'root',
				'password' => null,
				'port' => null,
				'driverOptions' => array()
			)
		);

		$connections = array();
		foreach ($options as $name => $connection) {
			$connections[$name] = array_replace_recursive($connectionTemplate, $connection);
		}

		$this->_dbalConfiguration = $connections;
	}

	/**
	 * Prepare Doctrine ORM configuration.
	 *
	 * @param array $options Doctrine ORM configuration.
	 */
	public function setOrm(array $options)
	{
		if (isset($options['defaultEntityManager'])) {
			$this->_defaultEntityManager = $options['defaultEntityManager'];
			unset($options['defaultEntityManager']);
		}

		$entityManagerTemplate = array(
			'entityManagerClass' => 'Doctrine\ORM\EntityManager',
			'configurationClass' => 'Doctrine\ORM\Configuration',
			'entityNamespaces' => array(),
			'connection' => $this->_defaultConnection,
			'proxy' => array(
				'autoGenerateClasses' => true,
				'namespace' => 'Application\Model\Proxy',
				'dir' => realpath(APPLICATION_PATH . '/models/proxies'),
			),
			'queryCache' => $this->_defaultCacheInstance,
			'resultCache' => $this->_defaultCacheInstance,
			'metadataCache' => $this->_defaultCacheInstance,
			'metadataDrivers' => array(),
			'DQLFunctions' => array(
				'numeric' => array(),
				'datetime' => array(),
				'string' => array()
			)
		);

		$entityManagers = array();
		foreach ($options as $name => $entityManager) {
			$entityManagers[$name] = array_replace_recursive($entityManagerTemplate, $entityManager);
		}

		$this->_ormConfiguration = $entityManagers;
	}

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
		if (is_null($connName)) {
			$connName = $this->_defaultConnection;
		}

		// Check if DBAL Connection has not yet been initialized
		if (!isset($this->_connections[$connName])) {
			// Check if DBAL Connection is configured
			if (!isset($this->_dbalConfiguration[$connName])) {
				throw new Exception\NameNotFoundException("Unable to find Doctrine DBAL Connection '{$connName}'.");
			}

			$this->_connections[$connName] = $this->_startDBALConnection($this->_dbalConfiguration[$connName]);
		}

		return $this->_connections[$connName];
	}

	/**
	 * Retrieves a list of names for all configured Connections
	 *
	 * @return array
	 */
	public function getConnectionNames()
	{
		return array_keys($this->_dbalConfiguration);
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
		if (is_null($cacheName)) {
			$cacheName = $this->_defaultCacheInstance;
		}

		// Check if Cache Instance has not yet been initialized
		if (!isset($this->_cacheInstances[$cacheName])) {
			// Check if Cache Instance is configured
			if (!isset($this->_cacheConfiguration[$cacheName])) {
				throw new Exception\NameNotFoundException("Unable to find Doctrine Cache Instance '{$cacheName}'.");
			}

			$this->_cacheInstances[$cacheName] = $this->_startCacheInstance($this->_cacheConfiguration[$cacheName]);
		}

		return $this->_cacheInstances[$cacheName];
	}

	/**
	 * Retrieves a list of names for all cache instances configured
	 *
	 * @return array
	 */
	public function getCacheInstanceNames()
	{
		return array_keys($this->_cacheConfiguration);
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
		if (is_null($emName)) {
			$emName = $this->_defaultEntityManager;
		}

		// Check if ORM Entity Manager has not yet been initialized
		if (!isset($this->_entityManagers[$emName])) {
			// Check if ORM EntityManager is configured
			if (!isset($this->_ormConfiguration[$emName])) {
				throw new Exception\NameNotFoundException("Unable to find Doctrine ORM EntityManager '{$emName}'.");
			}

			$this->_entityManagers[$emName] = $this->_startORMEntityManager($this->_ormConfiguration[$emName]);
		}

		return $this->_entityManagers[$emName];
	}

	/**
	 * Retrieves a list of names for all Entity Managers configured and/or loaded
	 *
	 * @return array
	 */
	public function getEntityManagerNames()
	{
		return array_keys($this->_ormConfiguration);
	}

	/**
	 * Initialize the DBAL Connection.
	 *
	 * @param array $config DBAL Connection configuration.
	 *
	 * @return Doctrine\DBAL\Connection
	 */
	protected function _startDBALConnection(array $config = array())
	{
		return \Doctrine\DBAL\DriverManager::getConnection(
			$config['parameters'],
			$this->_startDBALConfiguration($config),
			$this->_startDBALEventManager($config)
		);
	}

	/**
	 * Initialize the DBAL Configuration.
	 *
	 * @param array $config DBAL Connection configuration.
	 *
	 * @return Doctrine\DBAL\Configuration
	 */
	protected function _startDBALConfiguration(array $config = array())
	{
		$configClass = $config['configurationClass'];
		$configuration = new $configClass();

		// SQL Logger configuration
		if (!empty($config['sqlLoggerClass'])) {
			$sqlLoggerClass = $config['sqlLoggerClass'];
			$configuration->setSQLLogger(new $sqlLoggerClass());
		}

		// DBAL Types configuration
		$types = $config['types'];

		foreach ($types as $name => $className) {
			Type::overrideType($name, $className);
		}

		return $configuration;
	}

	/**
	 * Initialize the EventManager.
	 *
	 * @param array $config DBAL Connection configuration.
	 *
	 * @return Doctrine\Common\EventManager
	 */
	protected function _startDBALEventManager(array $config = array())
	{
		$eventManagerClass = $config['eventManagerClass'];
		$eventManager = new $eventManagerClass();

		// Event Subscribers configuration
		foreach ($config['eventSubscribers'] as $subscriber) {
			if ($subscriber) {
				$eventManager->addEventSubscriber(new $subscriber());
			}
		}

		return $eventManager;
	}

	/**
	 * Initialize Cache Instance.
	 *
	 * @param array $config Cache Instance configuration.
	 *
	 * @return Doctrine\Common\Cache\Cache
	 */
	protected function _startCacheInstance(array $config = array())
	{
		$adapterClass = $config['adapterClass'];
		$adapter = new $adapterClass();

		// Define namespace for cache
		if (isset($config['namespace']) && ! empty($config['namespace'])) {
			$adapter->setNamespace($config['namespace']);
		}

		if (method_exists($adapter, 'initialize')) {
			$adapter->initialize($config);
		} else if ($adapter instanceof \Doctrine\Common\Cache\MemcacheCache) {
			// Prevent stupid PHP error of missing extension (if other driver is being used)
			$memcacheClassName = 'Memcache';
			$memcache = new $memcacheClassName();

			// Default server configuration
			$defaultServer = array(
				'host'          => 'localhost',
				'port'          => 11211,
				'persistent'    => true,
				'weight'        => 1,
				'timeout'       => 1,
				'retryInterval' => 15,
				'status'        => true
			);

			if (isset($config['options']['servers'])) {
				foreach ($config['options']['servers'] as $server) {
					$server = array_replace_recursive($defaultServer, $server);

					$memcache->addServer(
						$server['host'],
						$server['port'],
						$server['persistent'],
						$server['weight'],
						$server['timeout'],
						$server['retryInterval'],
						$server['status']
					);
				}
			}

			$adapter->setMemcache($memcache);
		}

		return $adapter;
	}

	/**
	 * Initialize ORM EntityManager.
	 *
	 * @param array $config ORM EntityManager configuration.
	 *
	 * @return Doctrine\ORM\EntityManager
	 */
	protected function _startORMEntityManager(array $config = array())
	{
		if (isset($config['entityManagerClass'])) {
			$entityManagerClass = $config['entityManagerClass'];
		} else {
			$entityManagerClass = '\Doctrine\ORM\EntityManager';
		}

		return $entityManagerClass::create(
			$this->getConnection($config['connection']),
			$this->_startORMConfiguration($config)
		);
	}

	/**
	 * Initialize ORM Configuration.
	 *
	 * @param array $config ORM EntityManager configuration.
	 *
	 * @return Doctrine\ORM\Configuration
	 */
	protected function _startORMConfiguration(array $config = array())
	{
		$configClass = $config['configurationClass'];
		$configuration = new $configClass();

		// Entity Namespaces configuration
		foreach ($config['entityNamespaces'] as $alias => $namespace) {
			$configuration->addEntityNamespace($alias, $namespace);
		}

		// Proxy configuration
		$configuration->setAutoGenerateProxyClasses(
			!in_array($config['proxy']['autoGenerateClasses'], array("0", "false", false))
		);
		$configuration->setProxyNamespace($config['proxy']['namespace']);
		$configuration->setProxyDir($config['proxy']['dir']);

		// Cache configuration
		$configuration->setMetadataCacheImpl($this->getCacheInstance($config['metadataCache']));
		$configuration->setResultCacheImpl($this->getCacheInstance($config['resultCache']));
		$configuration->setQueryCacheImpl($this->getCacheInstance($config['queryCache']));

		// Metadata configuration
		$configuration->setMetadataDriverImpl($this->_startORMMetadata($config['metadataDrivers']));

		// DQL Functions configuration
		$dqlFunctions = $config['DQLFunctions'];

		foreach ($dqlFunctions['datetime'] as $name => $className) {
			$configuration->addCustomDatetimeFunction($name, $className);
		}

		foreach ($dqlFunctions['numeric'] as $name => $className) {
			$configuration->addCustomNumericFunction($name, $className);
		}

		foreach ($dqlFunctions['string'] as $name => $className) {
			$configuration->addCustomStringFunction($name, $className);
		}

		return $configuration;
	}

	/**
	 * Initialize ORM Metadata drivers.
	 *
	 * @param array $config ORM Mapping drivers.
	 *
	 * @return Doctrine\ORM\Mapping\Driver\DriverChain
	 */
	protected function _startORMMetadata(array $config = array())
	{
		$metadataDriver = new \Doctrine\ORM\Mapping\Driver\DriverChain();

		// Default metadata driver configuration
		$defaultMetadataDriver = array(
			'adapterClass'               => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
			'mappingNamespace'           => '',
			'mappingDirs'                => array(),
			'annotationReaderClass'      => 'Doctrine\Common\Annotations\AnnotationReader',
			'annotationReaderCache'      => $this->_defaultCacheInstance,
			'annotationReaderNamespaces' => array()
		);

		// Setup AnnotationRegistry
		if (isset($config['annotationRegistry'])) {
			$this->_startAnnotationRegistry($config['annotationRegistry']);
		}

		foreach ($config['drivers'] as $driver) {
			$driver = array_replace_recursive($defaultMetadataDriver, $driver);

			$reflClass = new \ReflectionClass($driver['adapterClass']);
			$nestedDriver = null;

			if (
				$reflClass->getName() == 'Doctrine\ORM\Mapping\Driver\AnnotationDriver' ||
				$reflClass->isSubclassOf('Doctrine\ORM\Mapping\Driver\AnnotationDriver')
			) {
				$annotationReaderClass = $driver['annotationReaderClass'];
				$annotationReader = new $annotationReaderClass();

				if (method_exists($annotationReader, 'setDefaultAnnotationNamespace')) {
					$annotationReader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
				}

				if (method_exists($annotationReader, 'setAnnotationNamespaceAlias')) {
					$driver['annotationReaderNamespaces']['ORM'] = 'Doctrine\ORM\Mapping\\';

					foreach ($driver['annotationReaderNamespaces'] as $alias => $namespace) {
						$annotationReader->setAnnotationNamespaceAlias($namespace, $alias);
					}
				}

				$indexedReader = new \Doctrine\Common\Annotations\CachedReader(
					new \Doctrine\Common\Annotations\IndexedReader($annotationReader),
					$this->getCacheInstance($driver['annotationReaderCache'])
				);

				$nestedDriver = $reflClass->newInstance($indexedReader, $driver['mappingDirs']);
			} else {
				$nestedDriver = $reflClass->newInstance($driver['mappingDirs']);
			}

			$metadataDriver->addDriver($nestedDriver, $driver['mappingNamespace']);
		}

		if (($drivers = $metadataDriver->getDrivers()) && count($drivers) == 1) {
			reset($drivers);
			$metadataDriver = $drivers[key($drivers)];
		}

		return $metadataDriver;
	}

	/**
	 * Initialize ORM Metatada Annotation Registry driver
	 *
	 * @param array $config  ORM Annotation Registry configuration.
	 */
	protected function _startAnnotationRegistry($config)
	{
		// Load annotations from Files
		if (isset($config['annotationFiles']) && is_array($config['annotationFiles'])) {
			foreach($config['annotationFiles'] as $file) {
				AnnotationRegistry::registerFile($file);
			}
		}

		// Load annotation namespaces
		if (isset($config['annotationNamespaces']) && is_array($config['annotationNamespaces'])) {
			foreach($config['annotationNamespaces'] as $annotationNamespace) {
				AnnotationRegistry::registerAutoloadNamespace(
					$annotationNamespace['namespace'],
					$annotationNamespace['includePath']
				);
			}
		}
	}

}

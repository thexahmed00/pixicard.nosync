import { openDB } from 'idb';

const env = import.meta.env;
const DB_NAME = env.VITE_APP_DB_NAME;
const DB_VERSION = env.VITE_APP_DB_VERSION;

/**
 * Tables to create in the database
 */
const tables = [
    {
        name: 'configurations',
        options: { keyPath: 'id', autoIncrement: false },
    },
    {
        name: 'locales_currencies',
        options: { keyPath: 'id', autoIncrement: false },
    },
    {
        name: 'countries_states',
        options: { keyPath: 'id', autoIncrement: false },
    },
    {
        name: 'products',
        options: { keyPath: 'id', autoIncrement: false },
    },
    {
        name: 'categories',
        options: { keyPath: 'id', autoIncrement: false },
    },
    {
        name: 'customers',
        options: { keyPath: 'id', autoIncrement: false },
    },
    {
        name: 'current_customer',
        options: { keyPath: 'id', autoIncrement: false },
    },
    {
        name: 'orders',
        options: { keyPath: 'id', autoIncrement: false },
    },
    {
        name: 'agents',
        options: { keyPath: 'id', autoIncrement: false },
    },
    {
        name: 'carts',
        options: { keyPath: 'id', autoIncrement: true },
    },
    {
        name: 'hold_orders',
        options: { keyPath: 'id', autoIncrement: true },
    },
    {
        name: 'offline_orders',
        options: { keyPath: 'id', autoIncrement: true },
    },
    {
        name: 'offline_customers',
        options: { keyPath: 'id', autoIncrement: true },
    }
];

/**
 * Open the database and create the tables if they don't exist
 */
const dbPromise = openDB(DB_NAME, DB_VERSION, {
    upgrade(db) {
        tables.forEach(table => {
            if (! db.objectStoreNames.contains(table.name)) {
                db.createObjectStore(table.name, table.options);
            }
        });
    },
});

/**
 * Composable function to interact with the IndexedDB
 */
export const useIndexedDB = () => {
    /**
     * Get the database instance
     */
    const getDb = async () => {
        return await dbPromise;
    };

    /**
     * Get all items from a table
     */
    const getAllItems = async (tableName) => {
        const db = await getDb();

        return await db.getAll(tableName);
    };

    /**
     * Get a single item from a table by ID
     */
    const getItem = async (tableName, id) => {
        const db = await getDb();

        return await db.get(tableName, id);
    };

    /**
     * Add an item to a table
     */
    const addItem = async (tableName, item) => {
        const db = await getDb();

        return await db.add(tableName, item);
    };

    /**
     * Update an item in a table
     */
    const updateItem = async (tableName, item) => {
        const db = await getDb();

        return await db.put(tableName, item);
    };

    /**
     * Delete an item from a table
     */
    const deleteItem = async (tableName, id) => {
        const db = await getDb();

        return await db.delete(tableName, id);
    };

    /**
     * Delete all items from a table
     */
    const deleteAllItems = async (tableName) => {
        const db = await getDb();

        return await db.clear(tableName);
    }

    /**
     * Get the current customer
     */
    const getCurrentCustomer = async () => {
        const db = await getDb();

        const customer = await db.getAll('current_customer');

        return customer?.[0] || null;
    };

    return {
        getAllItems,
        getItem,
        addItem,
        updateItem,
        deleteItem,
        deleteAllItems,
        getCurrentCustomer,
    };
}

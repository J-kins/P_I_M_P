/**
 * P.I.M.P - Schema Loader
 * Loads and manages chart/graph schemas from JSON files
 */

class SchemaLoader {
    constructor(pimp) {
        this.pimp = pimp || window.PIMP;
        this.schemas = new Map();
        this.basePath = '/static/charts/';
        this.loadingPromises = new Map();
    }

    /**
     * Load a schema from a JSON file
     * @param {string} schemaName - Name of the schema file (without .json extension)
     * @param {string} type - Type of schema: 'chart', 'graph', 'tree', 'map'
     * @returns {Promise<Object>} The loaded schema
     */
    async loadSchema(schemaName, type = 'chart') {
        const cacheKey = `${type}:${schemaName}`;

        // Return cached schema if available
        if (this.schemas.has(cacheKey)) {
            return this.schemas.get(cacheKey);
        }

        // Return existing loading promise if already loading
        if (this.loadingPromises.has(cacheKey)) {
            return this.loadingPromises.get(cacheKey);
        }

        // Determine path based on type
        let path;
        switch (type) {
            case 'chart':
                path = `${this.basePath}charts/${schemaName}.chartcomp.json`;
                break;
            case 'graph':
                path = `${this.basePath}graphs/${schemaName}.json`;
                break;
            case 'tree':
                path = `${this.basePath}trees/${schemaName}.json`;
                break;
            case 'map':
                path = `${this.basePath}maps/${schemaName}.json`;
                break;
            default:
                path = `${this.basePath}${schemaName}.json`;
        }

        // Create loading promise
        const loadPromise = this.fetchSchema(path, cacheKey);
        this.loadingPromises.set(cacheKey, loadPromise);

        try {
            const schema = await loadPromise;
            this.loadingPromises.delete(cacheKey);
            return schema;
        } catch (error) {
            this.loadingPromises.delete(cacheKey);
            throw error;
        }
    }

    /**
     * Fetch schema from server
     */
    async fetchSchema(path, cacheKey) {
        try {
            const response = await fetch(path);
            
            if (!response.ok) {
                throw new Error(`Failed to load schema: ${response.statusText}`);
            }

            const schema = await response.json();
            
            // Cache the schema
            this.schemas.set(cacheKey, schema);
            
            return schema;
        } catch (error) {
            console.error(`Error loading schema from ${path}:`, error);
            throw error;
        }
    }

    /**
     * Load multiple schemas
     * @param {Array<{name: string, type: string}>} schemaList
     * @returns {Promise<Object>} Object with schema names as keys
     */
    async loadSchemas(schemaList) {
        const promises = schemaList.map(({ name, type = 'chart' }) => 
            this.loadSchema(name, type).then(schema => ({ name, type, schema }))
        );

        const results = await Promise.all(promises);
        const schemas = {};

        results.forEach(({ name, type, schema }) => {
            schemas[`${type}:${name}`] = schema;
        });

        return schemas;
    }

    /**
     * Get cached schema
     * @param {string} schemaName
     * @param {string} type
     * @returns {Object|null}
     */
    getSchema(schemaName, type = 'chart') {
        const cacheKey = `${type}:${schemaName}`;
        return this.schemas.get(cacheKey) || null;
    }

    /**
     * Check if schema is loaded
     * @param {string} schemaName
     * @param {string} type
     * @returns {boolean}
     */
    hasSchema(schemaName, type = 'chart') {
        const cacheKey = `${type}:${schemaName}`;
        return this.schemas.has(cacheKey);
    }

    /**
     * Clear schema cache
     * @param {string} schemaName - Optional, clear specific schema
     * @param {string} type - Optional, filter by type
     */
    clearCache(schemaName = null, type = null) {
        if (schemaName && type) {
            const cacheKey = `${type}:${schemaName}`;
            this.schemas.delete(cacheKey);
        } else if (type) {
            // Clear all schemas of this type
            for (const key of this.schemas.keys()) {
                if (key.startsWith(`${type}:`)) {
                    this.schemas.delete(key);
                }
            }
        } else {
            // Clear all
            this.schemas.clear();
        }
    }

    /**
     * Preload common schemas
     * @param {Array<string>} schemaNames
     * @param {string} type
     */
    async preloadSchemas(schemaNames, type = 'chart') {
        const promises = schemaNames.map(name => this.loadSchema(name, type));
        await Promise.all(promises);
    }

    /**
     * Get all loaded schema names
     * @returns {Array<string>}
     */
    getLoadedSchemas() {
        return Array.from(this.schemas.keys());
    }

    /**
     * Validate schema structure
     * @param {Object} schema
     * @returns {boolean}
     */
    validateSchema(schema) {
        if (!schema || typeof schema !== 'object') {
            return false;
        }

        // Basic validation - can be extended based on schema structure
        return true;
    }

    /**
     * Merge schema with data
     * @param {Object} schema
     * @param {Object} data
     * @returns {Object}
     */
    mergeSchemaWithData(schema, data) {
        if (!schema || !data) {
            return schema || {};
        }

        // Deep merge schema with data
        return this.deepMerge(schema, data);
    }

    /**
     * Deep merge two objects
     */
    deepMerge(target, source) {
        const output = { ...target };

        if (this.isObject(target) && this.isObject(source)) {
            Object.keys(source).forEach(key => {
                if (this.isObject(source[key])) {
                    if (!(key in target)) {
                        Object.assign(output, { [key]: source[key] });
                    } else {
                        output[key] = this.deepMerge(target[key], source[key]);
                    }
                } else {
                    Object.assign(output, { [key]: source[key] });
                }
            });
        }

        return output;
    }

    isObject(item) {
        return item && typeof item === 'object' && !Array.isArray(item);
    }
}

// Initialize and attach to window
if (typeof window !== 'undefined') {
    window.SchemaLoader = new SchemaLoader(window.PIMP);
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SchemaLoader;
}



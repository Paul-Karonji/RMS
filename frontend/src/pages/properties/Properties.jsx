import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import {
  PlusIcon,
  MagnifyingGlassIcon,
  FunnelIcon,
  Squares2X2Icon,
  ListBulletIcon,
} from '@heroicons/react/24/outline';
import { Button, Card, LoadingSpinner, Alert } from '../../components/common';
import { PropertyCard } from '../../components/properties';
import propertyService from '../../services/propertyService';
import { useAuth } from '../../hooks/useAuth';

const PROPERTY_TYPES = [
  { value: '', label: 'All Types' },
  { value: 'apartment', label: 'Apartment' },
  { value: 'house', label: 'House' },
  { value: 'office', label: 'Office' },
  { value: 'shop', label: 'Shop' },
  { value: 'warehouse', label: 'Warehouse' },
];

const STATUS_OPTIONS = [
  { value: '', label: 'All Status' },
  { value: 'pending_approval', label: 'Pending Approval' },
  { value: 'approved', label: 'Approved' },
  { value: 'rejected', label: 'Rejected' },
];

const Properties = () => {
  const navigate = useNavigate();
  const { user } = useAuth();

  const [properties, setProperties] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Filters
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [typeFilter, setTypeFilter] = useState('');
  const [viewMode, setViewMode] = useState('grid'); // 'grid' or 'list'

  // Pagination
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [total, setTotal] = useState(0);

  const fetchProperties = async () => {
    setLoading(true);
    setError(null);

    try {
      const filters = {
        page: currentPage,
        per_page: 12,
      };

      if (search) filters.search = search;
      if (statusFilter) filters.status = statusFilter;
      if (typeFilter) filters.property_type = typeFilter;

      const response = await propertyService.getProperties(filters);

      if (response.success) {
        setProperties(response.data || []);
        if (response.meta) {
          setTotalPages(response.meta.last_page || 1);
          setTotal(response.meta.total || 0);
        }
      } else {
        setError(response.message || 'Failed to fetch properties');
      }
    } catch (err) {
      setError(err.message || 'Failed to fetch properties');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchProperties();
  }, [currentPage, statusFilter, typeFilter]);

  // Debounced search
  useEffect(() => {
    const timer = setTimeout(() => {
      if (currentPage === 1) {
        fetchProperties();
      } else {
        setCurrentPage(1);
      }
    }, 500);

    return () => clearTimeout(timer);
  }, [search]);

  const handleDelete = async (property) => {
    if (!window.confirm(`Are you sure you want to delete "${property.property_name || property.name}"?`)) {
      return;
    }

    try {
      const response = await propertyService.deleteProperty(property.id);
      if (response.success) {
        toast.success('Property deleted successfully');
        fetchProperties();
      } else {
        toast.error(response.message || 'Failed to delete property');
      }
    } catch (err) {
      toast.error(err.message || 'Failed to delete property');
    }
  };

  const canAddProperty = user?.role === 'property_owner' || user?.role === 'company_admin';

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-text">Properties</h1>
          <p className="text-muted mt-1">
            Manage your property portfolio
          </p>
        </div>

        {canAddProperty && (
          <Button onClick={() => navigate('/company/properties/create')}>
            <PlusIcon className="w-5 h-5 mr-2" />
            Add Property
          </Button>
        )}
      </div>

      {/* Filters */}
      <Card padding="md">
        <div className="flex flex-col lg:flex-row gap-4">
          {/* Search */}
          <div className="flex-1 relative">
            <MagnifyingGlassIcon className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-muted" />
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search properties..."
              className="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 hover:border-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-text placeholder:text-muted"
            />
          </div>

          {/* Status Filter */}
          <div className="flex items-center gap-2">
            <FunnelIcon className="w-5 h-5 text-muted" />
            <select
              value={statusFilter}
              onChange={(e) => {
                setStatusFilter(e.target.value);
                setCurrentPage(1);
              }}
              className="px-4 py-2.5 rounded-lg border border-slate-300 hover:border-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-text bg-white"
            >
              {STATUS_OPTIONS.map((option) => (
                <option key={option.value} value={option.value}>
                  {option.label}
                </option>
              ))}
            </select>
          </div>

          {/* Type Filter */}
          <select
            value={typeFilter}
            onChange={(e) => {
              setTypeFilter(e.target.value);
              setCurrentPage(1);
            }}
            className="px-4 py-2.5 rounded-lg border border-slate-300 hover:border-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-text bg-white"
          >
            {PROPERTY_TYPES.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>

          {/* View Toggle */}
          <div className="flex items-center border border-slate-300 rounded-lg overflow-hidden">
            <button
              onClick={() => setViewMode('grid')}
              className={`p-2.5 ${
                viewMode === 'grid'
                  ? 'bg-primary text-white'
                  : 'bg-white text-muted hover:bg-slate-50'
              }`}
            >
              <Squares2X2Icon className="w-5 h-5" />
            </button>
            <button
              onClick={() => setViewMode('list')}
              className={`p-2.5 ${
                viewMode === 'list'
                  ? 'bg-primary text-white'
                  : 'bg-white text-muted hover:bg-slate-50'
              }`}
            >
              <ListBulletIcon className="w-5 h-5" />
            </button>
          </div>
        </div>
      </Card>

      {/* Error State */}
      {error && <Alert type="error" message={error} />}

      {/* Loading State */}
      {loading ? (
        <div className="flex justify-center py-12">
          <LoadingSpinner size="lg" />
        </div>
      ) : properties.length === 0 ? (
        /* Empty State */
        <Card padding="lg">
          <div className="text-center py-12">
            <div className="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <Squares2X2Icon className="w-8 h-8 text-muted" />
            </div>
            <h3 className="text-lg font-medium text-text mb-2">
              No properties found
            </h3>
            <p className="text-muted mb-6">
              {search || statusFilter || typeFilter
                ? 'Try adjusting your filters'
                : 'Get started by adding your first property'}
            </p>
            {canAddProperty && !search && !statusFilter && !typeFilter && (
              <Button onClick={() => navigate('/company/properties/create')}>
                <PlusIcon className="w-5 h-5 mr-2" />
                Add Property
              </Button>
            )}
          </div>
        </Card>
      ) : (
        <>
          {/* Results Count */}
          <p className="text-sm text-muted">
            Showing {properties.length} of {total} properties
          </p>

          {/* Properties Grid/List */}
          <div
            className={
              viewMode === 'grid'
                ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6'
                : 'space-y-4'
            }
          >
            {properties.map((property) => (
              <PropertyCard
                key={property.id}
                property={property}
                onDelete={handleDelete}
              />
            ))}
          </div>

          {/* Pagination */}
          {totalPages > 1 && (
            <div className="flex items-center justify-center gap-2 pt-6">
              <Button
                variant="secondary"
                size="sm"
                onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
                disabled={currentPage === 1}
              >
                Previous
              </Button>
              <span className="px-4 py-2 text-sm text-muted">
                Page {currentPage} of {totalPages}
              </span>
              <Button
                variant="secondary"
                size="sm"
                onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
                disabled={currentPage === totalPages}
              >
                Next
              </Button>
            </div>
          )}
        </>
      )}
    </div>
  );
};

export default Properties;

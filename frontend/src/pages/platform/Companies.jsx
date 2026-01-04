import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { platformService } from '../../services/platform';
import CompanyCard from '../../components/platform/CompanyCard';
import { toast } from 'react-toastify';

const Companies = () => {
  const [companies, setCompanies] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState({
    status: '',
    pricing_model: '',
    search: '',
  });

  useEffect(() => {
    fetchCompanies();
  }, [filters]);

  const fetchCompanies = async () => {
    try {
      setLoading(true);
      const response = await platformService.getCompanies(filters);
      setCompanies(response.data.data);
    } catch (error) {
      console.error('Failed to fetch companies:', error);
      toast.error('Failed to load companies');
    } finally {
      setLoading(false);
    }
  };

  const handleSuspend = async (id) => {
    if (!window.confirm('Are you sure you want to suspend this company?')) return;
    
    try {
      await platformService.suspendCompany(id);
      toast.success('Company suspended successfully');
      fetchCompanies();
    } catch (error) {
      console.error('Failed to suspend company:', error);
      toast.error('Failed to suspend company');
    }
  };

  const handleActivate = async (id) => {
    try {
      await platformService.activateCompany(id);
      toast.success('Company activated successfully');
      fetchCompanies();
    } catch (error) {
      console.error('Failed to activate company:', error);
      toast.error('Failed to activate company');
    }
  };

  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    setFilters(prev => ({ ...prev, [name]: value }));
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-bold text-gray-900">Companies</h1>
        <Link
          to="/platform/companies/create"
          className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        >
          Create Company
        </Link>
      </div>

      <div className="bg-white rounded-lg shadow p-4">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              type="text"
              name="search"
              value={filters.search}
              onChange={handleFilterChange}
              placeholder="Search by name or email..."
              className="w-full rounded-md border border-gray-300 px-3 py-2"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              name="status"
              value={filters.status}
              onChange={handleFilterChange}
              className="w-full rounded-md border border-gray-300 px-3 py-2"
            >
              <option value="">All Statuses</option>
              <option value="active">Active</option>
              <option value="suspended">Suspended</option>
              <option value="deleted">Deleted</option>
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Pricing Model</label>
            <select
              name="pricing_model"
              value={filters.pricing_model}
              onChange={handleFilterChange}
              className="w-full rounded-md border border-gray-300 px-3 py-2"
            >
              <option value="">All Models</option>
              <option value="payment_processing">Payment Processing</option>
              <option value="listings_only">Listings Only</option>
            </select>
          </div>
        </div>
      </div>

      {loading ? (
        <div className="flex items-center justify-center h-64">
          <div className="text-gray-500">Loading companies...</div>
        </div>
      ) : companies.length === 0 ? (
        <div className="bg-white rounded-lg shadow p-12 text-center">
          <p className="text-gray-500">No companies found</p>
          <Link
            to="/platform/companies/create"
            className="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            Create First Company
          </Link>
        </div>
      ) : (
        <div className="grid grid-cols-1 gap-6">
          {companies.map((company) => (
            <CompanyCard
              key={company.id}
              company={company}
              onSuspend={handleSuspend}
              onActivate={handleActivate}
            />
          ))}
        </div>
      )}
    </div>
  );
};

export default Companies;

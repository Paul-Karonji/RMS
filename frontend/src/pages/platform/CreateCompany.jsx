import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { platformService } from '../../services/platform';
import CompanyForm from '../../components/platform/CompanyForm';
import { toast } from 'react-toastify';

const CreateCompany = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [credentials, setCredentials] = useState(null);
  
  const handleSubmit = async (formData) => {
    setLoading(true);
    try {
      const response = await platformService.createCompany(formData);
      setCredentials(response.data.data.admin_credentials);
      toast.success('Company created successfully!');
    } catch (error) {
      console.error('Failed to create company:', error);
      const message = error.response?.data?.message || 'Failed to create company';
      toast.error(message);
    } finally {
      setLoading(false);
    }
  };
  
  if (credentials) {
    return (
      <div className="max-w-2xl mx-auto">
        <div className="bg-green-50 border border-green-200 rounded-lg p-6">
          <h2 className="text-2xl font-bold text-green-800 mb-4">
            Company Created Successfully!
          </h2>
          <div className="space-y-2 mb-4">
            <p className="text-gray-700">
              <strong>Email:</strong> {credentials.email}
            </p>
            <p className="text-gray-700">
              <strong>Temporary Password:</strong> 
              <code className="ml-2 px-2 py-1 bg-white rounded border border-green-300">
                {credentials.temporary_password}
              </code>
            </p>
          </div>
          <div className="bg-yellow-50 border border-yellow-200 rounded p-3 mb-4">
            <p className="text-sm text-yellow-800">
              ⚠️ Please save these credentials securely. The password will not be shown again.
              Share these with the company administrator.
            </p>
          </div>
          <div className="flex space-x-4">
            <button
              onClick={() => navigate('/platform/companies')}
              className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
            >
              Go to Companies
            </button>
            <button
              onClick={() => {
                setCredentials(null);
                setLoading(false);
              }}
              className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
            >
              Create Another
            </button>
          </div>
        </div>
      </div>
    );
  }
  
  return (
    <div>
      <div className="mb-6">
        <h1 className="text-3xl font-bold text-gray-900">Create New Company</h1>
        <p className="mt-2 text-gray-600">
          Add a new property management company to the platform
        </p>
      </div>
      <CompanyForm onSubmit={handleSubmit} loading={loading} />
    </div>
  );
};

export default CreateCompany;

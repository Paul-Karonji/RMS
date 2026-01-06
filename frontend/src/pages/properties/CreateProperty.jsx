import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { ArrowLeftIcon } from '@heroicons/react/24/outline';
import { Card, Button } from '../../components/common';
import { PropertyForm } from '../../components/properties';
import propertyService from '../../services/propertyService';

const CreateProperty = () => {
  const navigate = useNavigate();
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleSubmit = async (data) => {
    setIsLoading(true);
    setError(null);

    try {
      const response = await propertyService.createProperty(data);

      if (response.success) {
        toast.success('Property registered successfully. Awaiting approval.');
        navigate('/company/properties');
      } else {
        setError(response.message || 'Failed to create property');
      }
    } catch (err) {
      setError(err.message || 'Failed to create property');
      if (err.errors) {
        // Handle validation errors
        const errorMessages = Object.values(err.errors).flat().join(', ');
        setError(errorMessages);
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="max-w-3xl mx-auto space-y-6">
      {/* Back Button */}
      <button
        onClick={() => navigate('/company/properties')}
        className="flex items-center gap-2 text-muted hover:text-text transition-colors"
      >
        <ArrowLeftIcon className="w-4 h-4" />
        <span>Back to Properties</span>
      </button>

      {/* Page Header */}
      <div>
        <h1 className="text-2xl font-bold text-text">Add New Property</h1>
        <p className="text-muted mt-1">
          Register a new property for management. It will be reviewed before approval.
        </p>
      </div>

      {/* Form Card */}
      <Card padding="lg">
        <PropertyForm
          onSubmit={handleSubmit}
          isLoading={isLoading}
          error={error}
          submitLabel="Register Property"
        />
      </Card>
    </div>
  );
};

export default CreateProperty;

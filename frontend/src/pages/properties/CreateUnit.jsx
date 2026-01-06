import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import { ArrowLeftIcon } from '@heroicons/react/24/outline';
import { Card, Button, LoadingSpinner, Alert } from '../../components/common';
import { UnitForm } from '../../components/properties';
import propertyService from '../../services/propertyService';

const CreateUnit = () => {
  const navigate = useNavigate();
  const { id: propertyId } = useParams();

  const [property, setProperty] = useState(null);
  const [loading, setLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState(null);
  const [fetchError, setFetchError] = useState(null);

  useEffect(() => {
    const fetchProperty = async () => {
      try {
        const response = await propertyService.getProperty(propertyId);
        if (response.success) {
          setProperty(response.data);
          // Check if property is approved
          const status = response.data.approval_status || response.data.status;
          if (status !== 'approved') {
            setFetchError('Units can only be added to approved properties.');
          }
        } else {
          setFetchError(response.message || 'Failed to fetch property');
        }
      } catch (err) {
        setFetchError(err.message || 'Failed to fetch property');
      } finally {
        setLoading(false);
      }
    };

    fetchProperty();
  }, [propertyId]);

  const handleSubmit = async (data) => {
    setIsSubmitting(true);
    setError(null);

    try {
      const response = await propertyService.createUnit(propertyId, data);

      if (response.success) {
        toast.success('Unit added successfully');
        navigate(`/company/properties/${propertyId}`);
      } else {
        setError(response.message || 'Failed to create unit');
      }
    } catch (err) {
      setError(err.message || 'Failed to create unit');
      if (err.errors) {
        const errorMessages = Object.values(err.errors).flat().join(', ');
        setError(errorMessages);
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center py-12">
        <LoadingSpinner size="lg" />
      </div>
    );
  }

  if (fetchError) {
    return (
      <div className="max-w-3xl mx-auto space-y-6">
        <button
          onClick={() => navigate(`/company/properties/${propertyId}`)}
          className="flex items-center gap-2 text-muted hover:text-text transition-colors"
        >
          <ArrowLeftIcon className="w-4 h-4" />
          <span>Back to Property</span>
        </button>
        <Alert type="error" message={fetchError} />
      </div>
    );
  }

  return (
    <div className="max-w-3xl mx-auto space-y-6">
      {/* Back Button */}
      <button
        onClick={() => navigate(`/company/properties/${propertyId}`)}
        className="flex items-center gap-2 text-muted hover:text-text transition-colors"
      >
        <ArrowLeftIcon className="w-4 h-4" />
        <span>Back to Property</span>
      </button>

      {/* Page Header */}
      <div>
        <h1 className="text-2xl font-bold text-text">Add New Unit</h1>
        <p className="text-muted mt-1">
          Add a unit to <span className="font-medium">{property?.property_name || property?.name}</span>
        </p>
      </div>

      {/* Form Card */}
      <Card padding="lg">
        <UnitForm
          onSubmit={handleSubmit}
          isLoading={isSubmitting}
          error={error}
          submitLabel="Add Unit"
        />
      </Card>
    </div>
  );
};

export default CreateUnit;

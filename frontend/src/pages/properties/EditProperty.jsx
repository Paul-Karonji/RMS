import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import { ArrowLeftIcon } from '@heroicons/react/24/outline';
import { Card, Button, LoadingSpinner, Alert } from '../../components/common';
import { PropertyForm, PropertyStatusBadge } from '../../components/properties';
import propertyService from '../../services/propertyService';

const EditProperty = () => {
  const navigate = useNavigate();
  const { id } = useParams();

  const [property, setProperty] = useState(null);
  const [loading, setLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState(null);
  const [fetchError, setFetchError] = useState(null);

  useEffect(() => {
    const fetchProperty = async () => {
      try {
        const response = await propertyService.getProperty(id);
        if (response.success) {
          setProperty(response.data);
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
  }, [id]);

  const handleSubmit = async (data) => {
    setIsSubmitting(true);
    setError(null);

    try {
      const response = await propertyService.updateProperty(id, data);

      if (response.success) {
        toast.success('Property updated successfully');
        navigate(`/company/properties/${id}`);
      } else {
        setError(response.message || 'Failed to update property');
      }
    } catch (err) {
      setError(err.message || 'Failed to update property');
      if (err.errors) {
        const errorMessages = Object.values(err.errors).flat().join(', ');
        setError(errorMessages);
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  const canEdit = property?.approval_status === 'pending_approval' || 
                  property?.approval_status === 'rejected' ||
                  property?.status === 'pending_approval' ||
                  property?.status === 'rejected';

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
          onClick={() => navigate('/company/properties')}
          className="flex items-center gap-2 text-muted hover:text-text transition-colors"
        >
          <ArrowLeftIcon className="w-4 h-4" />
          <span>Back to Properties</span>
        </button>
        <Alert type="error" message={fetchError} />
      </div>
    );
  }

  return (
    <div className="max-w-3xl mx-auto space-y-6">
      {/* Back Button */}
      <button
        onClick={() => navigate(`/company/properties/${id}`)}
        className="flex items-center gap-2 text-muted hover:text-text transition-colors"
      >
        <ArrowLeftIcon className="w-4 h-4" />
        <span>Back to Property</span>
      </button>

      {/* Page Header */}
      <div className="flex items-start justify-between">
        <div>
          <h1 className="text-2xl font-bold text-text">Edit Property</h1>
          <p className="text-muted mt-1">
            {property?.property_name || property?.name}
          </p>
        </div>
        <PropertyStatusBadge status={property?.approval_status || property?.status} />
      </div>

      {/* Warning for approved properties */}
      {!canEdit && (
        <Alert
          type="warning"
          message="This property has been approved and cannot be edited. Contact support if you need to make changes."
        />
      )}

      {/* Form Card */}
      <Card padding="lg">
        {canEdit ? (
          <PropertyForm
            initialData={property}
            onSubmit={handleSubmit}
            isLoading={isSubmitting}
            error={error}
            submitLabel="Update Property"
            isEdit
          />
        ) : (
          <div className="space-y-4">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <p className="text-sm text-muted">Property Name</p>
                <p className="font-medium text-text">{property?.property_name || property?.name}</p>
              </div>
              <div>
                <p className="text-sm text-muted">Property Type</p>
                <p className="font-medium text-text capitalize">{property?.property_type}</p>
              </div>
              <div>
                <p className="text-sm text-muted">City</p>
                <p className="font-medium text-text">{property?.city}</p>
              </div>
              <div>
                <p className="text-sm text-muted">County</p>
                <p className="font-medium text-text">{property?.county}</p>
              </div>
              <div>
                <p className="text-sm text-muted">Total Units</p>
                <p className="font-medium text-text">{property?.total_units}</p>
              </div>
              <div>
                <p className="text-sm text-muted">Platform Fee</p>
                <p className="font-medium text-text">
                  {property?.fee_type === 'percentage'
                    ? `${property?.fee_value}%`
                    : `KES ${Number(property?.fee_value).toLocaleString()}`}
                </p>
              </div>
            </div>
            {property?.address && (
              <div>
                <p className="text-sm text-muted">Address</p>
                <p className="font-medium text-text">{property?.address}</p>
              </div>
            )}
            {property?.description && (
              <div>
                <p className="text-sm text-muted">Description</p>
                <p className="text-text">{property?.description}</p>
              </div>
            )}
          </div>
        )}
      </Card>
    </div>
  );
};

export default EditProperty;

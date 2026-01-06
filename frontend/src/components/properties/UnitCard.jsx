import { useNavigate } from 'react-router-dom';
import {
  HomeModernIcon,
  EllipsisVerticalIcon,
  StarIcon,
} from '@heroicons/react/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/react/24/solid';
import { useState, useRef, useEffect } from 'react';
import UnitStatusBadge from './UnitStatusBadge';

const UnitCard = ({ unit, onEdit, onDelete, showActions = true }) => {
  const navigate = useNavigate();
  const [menuOpen, setMenuOpen] = useState(false);
  const menuRef = useRef(null);

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (menuRef.current && !menuRef.current.contains(event.target)) {
        setMenuOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleCardClick = () => {
    navigate(`/company/units/${unit.id}`);
  };

  const handleMenuClick = (e) => {
    e.stopPropagation();
    setMenuOpen(!menuOpen);
  };

  const handleEdit = (e) => {
    e.stopPropagation();
    setMenuOpen(false);
    if (onEdit) {
      onEdit(unit);
    } else {
      navigate(`/company/units/${unit.id}/edit`);
    }
  };

  const handleDelete = (e) => {
    e.stopPropagation();
    setMenuOpen(false);
    if (onDelete) {
      onDelete(unit);
    }
  };

  const formatCurrency = (amount) => {
    return `KES ${Number(amount).toLocaleString()}`;
  };

  return (
    <div
      onClick={handleCardClick}
      className="bg-surface rounded-lg border border-slate-200 shadow-sm hover:shadow-md transition-shadow cursor-pointer overflow-hidden"
    >
      {/* Card Header */}
      <div className="p-4 border-b border-slate-100">
        <div className="flex items-start justify-between">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
              <HomeModernIcon className="w-5 h-5 text-blue-600" />
            </div>
            <div>
              <div className="flex items-center gap-2">
                <h3 className="font-semibold text-text">
                  Unit {unit.unit_number}
                </h3>
                {unit.is_featured && (
                  <StarIconSolid className="w-4 h-4 text-yellow-500" />
                )}
              </div>
              <p className="text-sm text-muted">{unit.unit_type}</p>
            </div>
          </div>

          {showActions && (
            <div className="relative" ref={menuRef}>
              <button
                onClick={handleMenuClick}
                className="p-1 rounded hover:bg-slate-100 transition-colors"
              >
                <EllipsisVerticalIcon className="w-5 h-5 text-muted" />
              </button>

              {menuOpen && (
                <div className="absolute right-0 mt-1 w-36 bg-surface rounded-lg shadow-lg border border-slate-200 py-1 z-10">
                  <button
                    onClick={handleEdit}
                    className="w-full px-4 py-2 text-left text-sm text-text hover:bg-slate-50"
                  >
                    Edit
                  </button>
                  <button
                    onClick={handleDelete}
                    className="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50"
                  >
                    Delete
                  </button>
                </div>
              )}
            </div>
          )}
        </div>
      </div>

      {/* Card Body */}
      <div className="p-4 space-y-3">
        {/* Bedrooms & Bathrooms */}
        <div className="flex items-center gap-4 text-sm text-muted">
          <span>{unit.bedrooms || 0} Bed</span>
          <span>•</span>
          <span>{unit.bathrooms || 0} Bath</span>
          {unit.square_feet && (
            <>
              <span>•</span>
              <span>{unit.square_feet} sqft</span>
            </>
          )}
        </div>

        {/* Floor */}
        {unit.floor_number && (
          <p className="text-sm text-muted">Floor {unit.floor_number}</p>
        )}

        {/* Status */}
        <div className="flex items-center justify-between pt-2">
          <UnitStatusBadge status={unit.status} />
        </div>
      </div>

      {/* Card Footer - Pricing */}
      <div className="px-4 py-3 bg-slate-50 border-t border-slate-100">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-xs text-muted">Rent</p>
            <p className="font-semibold text-text">
              {formatCurrency(unit.rent_amount)}
            </p>
          </div>
          <div className="text-right">
            <p className="text-xs text-muted">Deposit</p>
            <p className="font-medium text-muted">
              {formatCurrency(unit.deposit_amount)}
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default UnitCard;

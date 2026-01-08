import React from 'react';
import tenantService from '../../services/tenantService';

/**
 * ProRatedCalculation - Display pro-rated rent calculation preview
 * @param {string} startDate - Lease start date (YYYY-MM-DD)
 * @param {number} monthlyRent - Monthly rent amount
 * @param {number} depositAmount - Security deposit amount
 * @param {boolean} showDeposit - Whether to show deposit in calculation
 */
const ProRatedCalculation = ({
    startDate,
    monthlyRent,
    depositAmount = 0,
    showDeposit = true
}) => {
    if (!startDate || !monthlyRent) {
        return (
            <div className="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <p className="text-gray-500 text-sm">
                    Enter start date and rent amount to see payment breakdown
                </p>
            </div>
        );
    }

    const rentCalc = tenantService.calculateProRatedRent(startDate, monthlyRent);
    const total = showDeposit ? rentCalc.amount + depositAmount : rentCalc.amount;
    const formattedDate = new Date(startDate).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });

    return (
        <div className="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-5 border border-blue-100">
            <h4 className="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">
                First Payment Breakdown
            </h4>

            <div className="space-y-3">
                {/* Start Date */}
                <div className="flex justify-between items-center text-sm">
                    <span className="text-gray-600">Move-in Date:</span>
                    <span className="font-medium text-gray-900">{formattedDate}</span>
                </div>

                <hr className="border-gray-200" />

                {/* Pro-rated Rent */}
                <div className="flex justify-between items-start">
                    <div>
                        <span className="text-gray-700 font-medium">
                            {rentCalc.isProrated ? 'Prorated Rent' : 'First Month Rent'}
                        </span>
                        <p className="text-xs text-gray-500 mt-0.5">
                            {rentCalc.note}
                        </p>
                    </div>
                    <span className="font-semibold text-gray-900">
                        {tenantService.formatCurrency(rentCalc.amount)}
                    </span>
                </div>

                {/* Deposit */}
                {showDeposit && depositAmount > 0 && (
                    <div className="flex justify-between items-center">
                        <span className="text-gray-700 font-medium">Security Deposit</span>
                        <span className="font-semibold text-gray-900">
                            {tenantService.formatCurrency(depositAmount)}
                        </span>
                    </div>
                )}

                {/* Divider */}
                <hr className="border-gray-300 border-dashed" />

                {/* Total */}
                <div className="flex justify-between items-center">
                    <span className="text-gray-900 font-bold">Total First Payment</span>
                    <span className="text-lg font-bold text-blue-600">
                        {tenantService.formatCurrency(total)}
                    </span>
                </div>
            </div>

            {/* Pro-rated indicator */}
            {rentCalc.isProrated && (
                <div className="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <div className="flex items-start gap-2">
                        <svg className="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div className="text-sm text-yellow-800">
                            <p className="font-medium">Pro-rated Rent Applied</p>
                            <p className="text-yellow-700 mt-0.5">
                                Since the move-in date is after the 15th, only half of the monthly rent
                                ({tenantService.formatCurrency(monthlyRent)}) is charged for the first month.
                            </p>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default ProRatedCalculation;

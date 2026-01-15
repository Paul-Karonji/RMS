import { describe, it, expect, vi } from 'vitest'
import { render, screen } from '@testing-library/react'
import { BrowserRouter } from 'react-router-dom'
import CompanyDashboard from '../src/pages/dashboard/CompanyDashboard'
import { AuthProvider } from '../src/contexts/AuthContext'

// Mock API calls
vi.mock('../src/services/api', () => ({
    default: {
        get: vi.fn(),
    },
}))

const renderWithProviders = (component) => {
    return render(
        <BrowserRouter>
            <AuthProvider>
                {component}
            </AuthProvider>
        </BrowserRouter>
    )
}

describe('CompanyDashboard Component', () => {
    it('renders dashboard title', () => {
        renderWithProviders(<CompanyDashboard />)

        expect(screen.getByText(/dashboard/i)).toBeInTheDocument()
    })

    it('displays loading state initially', () => {
        renderWithProviders(<CompanyDashboard />)

        expect(screen.getByText(/loading/i)).toBeInTheDocument()
    })

    it('renders stat cards', async () => {
        const api = await import('../src/services/api')
        api.default.get.mockResolvedValue({
            data: {
                total_properties: 10,
                total_units: 50,
                occupied_units: 35,
                total_revenue: 500000,
            },
        })

        renderWithProviders(<CompanyDashboard />)

        // Wait for data to load
        await screen.findByText(/total properties/i)

        expect(screen.getByText('10')).toBeInTheDocument()
        expect(screen.getByText('50')).toBeInTheDocument()
    })

    it('calculates occupancy rate correctly', async () => {
        const api = await import('../src/services/api')
        api.default.get.mockResolvedValue({
            data: {
                total_units: 50,
                occupied_units: 35,
            },
        })

        renderWithProviders(<CompanyDashboard />)

        await screen.findByText(/occupancy rate/i)

        // 35/50 = 70%
        expect(screen.getByText(/70%/)).toBeInTheDocument()
    })

    it('handles API error gracefully', async () => {
        const api = await import('../src/services/api')
        api.default.get.mockRejectedValue(new Error('API Error'))

        renderWithProviders(<CompanyDashboard />)

        await screen.findByText(/error loading dashboard/i)
    })
})

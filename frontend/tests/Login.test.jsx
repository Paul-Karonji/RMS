import { describe, it, expect, vi, beforeEach } from 'vitest'
import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { BrowserRouter } from 'react-router-dom'
import Login from '../src/pages/auth/Login'
import { AuthProvider } from '../src/contexts/AuthContext'

// Mock authService
vi.mock('../src/services/authService', () => ({
    default: {
        login: vi.fn(),
    },
}))

const renderWithRouter = (component) => {
    return render(
        <BrowserRouter>
            <AuthProvider>
                {component}
            </AuthProvider>
        </BrowserRouter>
    )
}

describe('Login Component', () => {
    beforeEach(() => {
        vi.clearAllMocks()
    })

    it('renders login form', () => {
        renderWithRouter(<Login />)

        expect(screen.getByLabelText(/email/i)).toBeInTheDocument()
        expect(screen.getByLabelText(/password/i)).toBeInTheDocument()
        expect(screen.getByRole('button', { name: /sign in/i })).toBeInTheDocument()
    })

    it('displays validation errors for empty fields', async () => {
        const user = userEvent.setup()
        renderWithRouter(<Login />)

        const submitButton = screen.getByRole('button', { name: /sign in/i })
        await user.click(submitButton)

        await waitFor(() => {
            expect(screen.getByText(/email is required/i)).toBeInTheDocument()
        })
    })

    it('displays validation error for invalid email', async () => {
        const user = userEvent.setup()
        renderWithRouter(<Login />)

        const emailInput = screen.getByLabelText(/email/i)
        await user.type(emailInput, 'invalid-email')

        const submitButton = screen.getByRole('button', { name: /sign in/i })
        await user.click(submitButton)

        await waitFor(() => {
            expect(screen.getByText(/invalid email/i)).toBeInTheDocument()
        })
    })

    it('submits form with valid credentials', async () => {
        const user = userEvent.setup()
        const authService = await import('../src/services/authService')
        authService.default.login.mockResolvedValue({
            user: { id: '1', email: 'test@example.com' },
            token: 'fake-token',
        })

        renderWithRouter(<Login />)

        await user.type(screen.getByLabelText(/email/i), 'test@example.com')
        await user.type(screen.getByLabelText(/password/i), 'password123')
        await user.click(screen.getByRole('button', { name: /sign in/i }))

        await waitFor(() => {
            expect(authService.default.login).toHaveBeenCalledWith({
                email: 'test@example.com',
                password: 'password123',
            })
        })
    })

    it('displays error message on login failure', async () => {
        const user = userEvent.setup()
        const authService = await import('../src/services/authService')
        authService.default.login.mockRejectedValue(new Error('Invalid credentials'))

        renderWithRouter(<Login />)

        await user.type(screen.getByLabelText(/email/i), 'test@example.com')
        await user.type(screen.getByLabelText(/password/i), 'wrongpassword')
        await user.click(screen.getByRole('button', { name: /sign in/i }))

        await waitFor(() => {
            expect(screen.getByText(/invalid credentials/i)).toBeInTheDocument()
        })
    })

    it('has link to register page', () => {
        renderWithRouter(<Login />)

        const registerLink = screen.getByText(/don't have an account/i)
        expect(registerLink).toBeInTheDocument()
    })
})

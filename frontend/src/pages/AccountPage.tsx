import React, { useState } from 'react'
import { useAppSelector, useAppDispatch } from '../store'
import { selectUser, selectIsAuthenticated, logout } from '../store/slices/authSlice'
import { User, Mail, Calendar, MapPin, Phone, Edit, Save, X, LogOut } from 'lucide-react'

export const AccountPage: React.FC = () => {
  const dispatch = useAppDispatch()
  const user = useAppSelector(selectUser)
  const isAuthenticated = useAppSelector(selectIsAuthenticated)
  const [isEditing, setIsEditing] = useState(false)
  const [editForm, setEditForm] = useState({
    name: user?.name || '',
    email: user?.email || '',
    phone: '',
    address: '',
    birthdate: user?.birthdate || ''
  })

  const handleLogout = () => {
    dispatch(logout())
  }

  const handleSave = () => {
    // TODO: Implement API call to update user profile
    console.log('Saving user data:', editForm)
    setIsEditing(false)
  }

  const handleCancel = () => {
    setEditForm({
      name: user?.name || '',
      email: user?.email || '',
      phone: '',
      address: '',
      birthdate: user?.birthdate || ''
    })
    setIsEditing(false)
  }

  if (!isAuthenticated || !user) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="text-center">
          <h1 className="text-4xl font-bold mb-4">My Account</h1>
          <p className="text-muted-foreground mb-8">
            Please log in to view your account details.
          </p>
          <a 
            href="/login" 
            className="inline-flex items-center px-6 py-3 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
          >
            Sign In
          </a>
        </div>
      </div>
    )
  }

  return (
    <div className="container mx-auto px-4 py-8 max-w-4xl">
      <div className="mb-8">
        <h1 className="text-4xl font-bold mb-2">My Account</h1>
        <p className="text-muted-foreground">
          Manage your profile and preferences
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Profile Card */}
        <div className="lg:col-span-2">
          <div className="bg-card rounded-lg shadow-sm border p-6">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-2xl font-semibold">Profile Information</h2>
              {!isEditing ? (
                <button
                  onClick={() => setIsEditing(true)}
                  className="flex items-center gap-2 px-4 py-2 text-sm bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
                >
                  <Edit className="h-4 w-4" />
                  Edit
                </button>
              ) : (
                <div className="flex gap-2">
                  <button
                    onClick={handleSave}
                    className="flex items-center gap-2 px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                  >
                    <Save className="h-4 w-4" />
                    Save
                  </button>
                  <button
                    onClick={handleCancel}
                    className="flex items-center gap-2 px-4 py-2 text-sm bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors"
                  >
                    <X className="h-4 w-4" />
                    Cancel
                  </button>
                </div>
              )}
            </div>

            <div className="space-y-6">
              {/* Avatar */}
              <div className="flex items-center gap-4">
                <div className="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center">
                  <User className="h-10 w-10 text-primary" />
                </div>
                <div>
                  <h3 className="text-lg font-semibold">{user.name}</h3>
                  <p className="text-muted-foreground">Member since {new Date(user.created_at).toLocaleDateString()}</p>
                </div>
              </div>

              {/* Form Fields */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm font-medium mb-2">Full Name</label>
                  {isEditing ? (
                    <input
                      type="text"
                      value={editForm.name}
                      onChange={(e) => setEditForm({ ...editForm, name: e.target.value })}
                      className="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                  ) : (
                    <div className="flex items-center gap-2 p-3 bg-muted/50 rounded-lg">
                      <User className="h-4 w-4 text-muted-foreground" />
                      <span>{user.name}</span>
                    </div>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium mb-2">Email Address</label>
                  {isEditing ? (
                    <input
                      type="email"
                      value={editForm.email}
                      onChange={(e) => setEditForm({ ...editForm, email: e.target.value })}
                      className="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                  ) : (
                    <div className="flex items-center gap-2 p-3 bg-muted/50 rounded-lg">
                      <Mail className="h-4 w-4 text-muted-foreground" />
                      <span>{user.email}</span>
                    </div>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium mb-2">Phone Number</label>
                  {isEditing ? (
                    <input
                      type="tel"
                      value={editForm.phone}
                      onChange={(e) => setEditForm({ ...editForm, phone: e.target.value })}
                      placeholder="Enter phone number"
                      className="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                  ) : (
                    <div className="flex items-center gap-2 p-3 bg-muted/50 rounded-lg">
                      <Phone className="h-4 w-4 text-muted-foreground" />
                      <span>{editForm.phone || 'Not provided'}</span>
                    </div>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium mb-2">Birth Date</label>
                  {isEditing ? (
                    <input
                      type="date"
                      value={editForm.birthdate}
                      onChange={(e) => setEditForm({ ...editForm, birthdate: e.target.value })}
                      className="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                  ) : (
                    <div className="flex items-center gap-2 p-3 bg-muted/50 rounded-lg">
                      <Calendar className="h-4 w-4 text-muted-foreground" />
                      <span>{user.birthdate ? new Date(user.birthdate).toLocaleDateString() : 'Not provided'}</span>
                    </div>
                  )}
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium mb-2">Address</label>
                {isEditing ? (
                  <textarea
                    value={editForm.address}
                    onChange={(e) => setEditForm({ ...editForm, address: e.target.value })}
                    placeholder="Enter your address"
                    rows={3}
                    className="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                  />
                ) : (
                  <div className="flex items-start gap-2 p-3 bg-muted/50 rounded-lg">
                    <MapPin className="h-4 w-4 text-muted-foreground mt-0.5" />
                    <span>{editForm.address || 'Not provided'}</span>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          {/* Account Stats */}
          <div className="bg-card rounded-lg shadow-sm border p-6">
            <h3 className="text-lg font-semibold mb-4">Account Overview</h3>
            <div className="space-y-4">
              <div className="flex justify-between items-center">
                <span className="text-muted-foreground">Gender</span>
                <span className="font-medium capitalize">{user.gender || 'Not specified'}</span>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-muted-foreground">Account Status</span>
                <span className="px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm">Active</span>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-muted-foreground">Total Orders</span>
                <span className="font-medium">0</span>
              </div>
            </div>
          </div>

          {/* Quick Actions */}
          <div className="bg-card rounded-lg shadow-sm border p-6">
            <h3 className="text-lg font-semibold mb-4">Quick Actions</h3>
            <div className="space-y-3">
              <a
                href="/orders"
                className="block w-full px-4 py-2 text-left bg-muted/50 hover:bg-muted rounded-lg transition-colors"
              >
                View Orders
              </a>
              <a
                href="/wishlist"
                className="block w-full px-4 py-2 text-left bg-muted/50 hover:bg-muted rounded-lg transition-colors"
              >
                My Wishlist
              </a>
              <a
                href="/cart"
                className="block w-full px-4 py-2 text-left bg-muted/50 hover:bg-muted rounded-lg transition-colors"
              >
                Shopping Cart
              </a>
            </div>
          </div>

          {/* Logout */}
          <div className="bg-card rounded-lg shadow-sm border p-6">
            <button
              onClick={handleLogout}
              className="flex items-center gap-2 w-full px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
            >
              <LogOut className="h-4 w-4" />
              Sign Out
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}
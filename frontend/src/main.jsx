import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
import {
  ArrowDownUp,
  ArrowRight,
  Box,
  Check,
  CircleAlert,
  LoaderCircle,
  LogOut,
  PackagePlus,
  Pencil,
  Plus,
  Search,
  ShieldCheck,
  Trash2,
  X,
} from 'lucide-react';
import './styles.css';

const API_BASE = (import.meta.env.VITE_API_BASE_URL || '').replace(/\/$/, '');

async function parseResponse(response) {
  if (response.status === 204) return null;
  return response.json().catch(() => ({}));
}

async function request(path, options = {}, canRefresh = true) {
  const headers = new Headers(options.headers || {});
  if (options.body && !headers.has('Content-Type')) headers.set('Content-Type', 'application/json');
  const accessToken = sessionStorage.getItem('access_token');
  if (accessToken) headers.set('Authorization', `Bearer ${accessToken}`);

  let response = await fetch(`${API_BASE}${path}`, { ...options, headers });
  if (response.status === 401 && canRefresh && !['/api/login', '/api/register'].includes(path)) {
    const refreshToken = sessionStorage.getItem('refresh_token');
    if (refreshToken) {
      const refreshed = await fetch(`${API_BASE}/api/refresh`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ refresh_token: refreshToken }),
      });
      const refreshData = await parseResponse(refreshed);
      if (refreshed.ok && refreshData?.tokens) {
        sessionStorage.setItem('access_token', refreshData.tokens.access_token);
        sessionStorage.setItem('refresh_token', refreshData.tokens.refresh_token);
        return request(path, options, false);
      }
      sessionStorage.removeItem('access_token');
      sessionStorage.removeItem('refresh_token');
      window.dispatchEvent(new Event('auth-expired'));
    }
  }

  const data = await parseResponse(response);
  if (!response.ok) throw new Error(data?.error || data?.message || 'The request could not be completed.');
  return data;
}

function money(value) {
  return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'USD' }).format(Number(value || 0));
}

function App() {
  const [authenticated, setAuthenticated] = useState(Boolean(sessionStorage.getItem('access_token')));
  const [authMode, setAuthMode] = useState('login');
  const [authMessage, setAuthMessage] = useState('');
  const [user, setUser] = useState(() => {
    try {
      return JSON.parse(sessionStorage.getItem('user') || 'null');
    } catch {
      return null;
    }
  });
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [query, setQuery] = useState('');
  const [sort, setSort] = useState('newest');
  const [dialog, setDialog] = useState(null);
  const [saving, setSaving] = useState(false);

  async function loadProducts() {
    setLoading(true);
    setError('');
    try {
      const result = await request('/api/products');
      setProducts(result.data || []);
    } catch (requestError) {
      setError(requestError.message);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    if (authenticated) loadProducts();
  }, [authenticated]);

  useEffect(() => {
    const expire = () => {
      setAuthenticated(false);
      setUser(null);
      setProducts([]);
      sessionStorage.removeItem('user');
      setError('Your session ended. Sign in again to continue.');
    };
    window.addEventListener('auth-expired', expire);
    return () => window.removeEventListener('auth-expired', expire);
  }, []);

  async function signIn(event) {
    event.preventDefault();
    setSaving(true);
    setError('');
    setAuthMessage('');
    const form = new FormData(event.currentTarget);
    try {
      const result = await request('/api/login', {
        method: 'POST',
        body: JSON.stringify({ username: form.get('username'), password: form.get('password') }),
      }, false);
      sessionStorage.setItem('access_token', result.tokens.access_token);
      sessionStorage.setItem('refresh_token', result.tokens.refresh_token);
      sessionStorage.setItem('user', JSON.stringify(result.user));
      setUser(result.user);
      setAuthenticated(true);
    } catch (requestError) {
      setError(requestError.message);
    } finally {
      setSaving(false);
    }
  }

  async function register(event) {
    event.preventDefault();
    setSaving(true);
    setError('');
    setAuthMessage('');
    const form = new FormData(event.currentTarget);
    try {
      const result = await request('/api/register', {
        method: 'POST',
        body: JSON.stringify({
          username: form.get('username'),
          email: form.get('email'),
          password: form.get('password'),
          password_confirmation: form.get('password_confirmation'),
        }),
      }, false);
      setAuthMode('login');
      setAuthMessage(result.message || 'Account created. You can now sign in.');
    } catch (requestError) {
      setError(requestError.message);
    } finally {
      setSaving(false);
    }
  }

  async function signOut() {
    const refreshToken = sessionStorage.getItem('refresh_token');
    try {
      await request('/api/logout', {
        method: 'POST',
        body: JSON.stringify({ refresh_token: refreshToken }),
      });
    } catch {
      // Clear local credentials even when the API is unavailable.
    }
    sessionStorage.removeItem('access_token');
    sessionStorage.removeItem('refresh_token');
    sessionStorage.removeItem('user');
    setAuthenticated(false);
    setUser(null);
    setProducts([]);
    setError('');
  }

  async function saveProduct(event) {
    event.preventDefault();
    setSaving(true);
    setError('');
    const form = new FormData(event.currentTarget);
    const payload = {
      product_name: form.get('product_name'),
      description: form.get('description'),
      price: form.get('price'),
      quantity: form.get('quantity'),
    };
    try {
      const editing = dialog?.product;
      await request(editing ? `/api/products/${editing.id}` : '/api/products', {
        method: editing ? 'PATCH' : 'POST',
        body: JSON.stringify(payload),
      });
      setDialog(null);
      await loadProducts();
    } catch (requestError) {
      setError(requestError.message);
    } finally {
      setSaving(false);
    }
  }

  async function deleteProduct(product) {
    if (!window.confirm(`Delete “${product.product_name}”? This cannot be undone.`)) return;
    setError('');
    try {
      await request(`/api/products/${product.id}`, { method: 'DELETE' });
      setProducts((current) => current.filter((item) => item.id !== product.id));
    } catch (requestError) {
      setError(requestError.message);
    }
  }

  const visibleProducts = products
    .filter((product) => `${product.product_name} ${product.description || ''}`.toLowerCase().includes(query.toLowerCase()))
    .sort((left, right) => sort === 'newest' ? Number(right.id) - Number(left.id) : left.product_name.localeCompare(right.product_name));
  const totalUnits = products.reduce((sum, product) => sum + Number(product.quantity || 0), 0);

  if (!authenticated) {
    return (
      <main className="auth-layout">
        <section className="auth-panel">
          <a className="brand" href="/" aria-label="Stockroom home">
            <span className="brand-mark"><Box size={20} strokeWidth={2.4} /></span>
            <span>stockroom<span className="brand-period">.</span></span>
          </a>
          <div className="auth-copy">
            <p className="eyebrow">PRODUCT OPERATIONS / {authMode === 'login' ? '01' : '02'}</p>
            <h1>{authMode === 'login' ? <>Good stock.<br /><em>Clear head.</em></> : <>Make room<br /><em>for good work.</em></>}</h1>
            <p className="auth-description">{authMode === 'login' ? 'Your product desk, connected to the LavaLust API.' : 'Create an account to manage your product inventory.'}</p>
          </div>
          <form className="login-form" onSubmit={authMode === 'login' ? signIn : register}>
            <label htmlFor="username">Username</label>
            <input id="username" name="username" autoComplete="username" required />
            {authMode === 'register' && <>
              <label htmlFor="email">Email</label>
              <input id="email" name="email" type="email" autoComplete="email" maxLength="255" required />
            </>}
            <label htmlFor="password">Password</label>
            <input id="password" name="password" type="password" autoComplete={authMode === 'login' ? 'current-password' : 'new-password'} minLength={authMode === 'register' ? 8 : undefined} required />
            {authMode === 'register' && <>
              <label htmlFor="password_confirmation">Confirm password</label>
              <input id="password_confirmation" name="password_confirmation" type="password" autoComplete="new-password" minLength="8" required />
            </>}
            {authMessage && <p className="form-success"><Check size={16} />{authMessage}</p>}
            {error && <p className="form-error"><CircleAlert size={16} />{error}</p>}
            <button className="button button-primary login-submit" type="submit" disabled={saving}>
              {saving ? <LoaderCircle className="spin" size={18} /> : <>{authMode === 'login' ? 'Sign in' : 'Create account'} <ArrowRight size={17} /></>}
            </button>
          </form>
          <p className="auth-foot">{authMode === 'login' ? 'New to Stockroom?' : 'Already have an account?'} <button className="auth-link" type="button" onClick={() => { setAuthMode(authMode === 'login' ? 'register' : 'login'); setError(''); setAuthMessage(''); }}>{authMode === 'login' ? 'Create an account' : 'Sign in'}</button></p>
        </section>
        <aside className="auth-art" aria-label="Inventory workspace preview">
          <div className="art-stamp"><ShieldCheck size={17} /> AUTHENTICATED WORKSPACE</div>
          <div className="art-graphic">
            <div className="graphic-orbit orbit-one" />
            <div className="graphic-orbit orbit-two" />
            <div className="box-illustration"><Box size={116} strokeWidth={0.8} /></div>
            <div className="graphic-note note-top"><span className="note-dot" /> live inventory</div>
            <div className="graphic-note note-bottom"><span className="note-bars"><i /><i /><i /></span> neatly in order</div>
          </div>
          <div className="art-footer"><span>DESIGNED FOR THE EVERYDAY COUNT</span><span>EST. 2025</span></div>
        </aside>
      </main>
    );
  }

  return (
    <main className="workspace">
      <header className="topbar">
        <a className="brand" href="/" aria-label="Stockroom home">
          <span className="brand-mark"><Box size={19} strokeWidth={2.4} /></span>
          <span>stockroom<span className="brand-period">.</span></span>
        </a>
        <div className="topbar-right">
          <div className="user-badge"><span className="avatar">{(user?.username || 'U').slice(0, 1).toUpperCase()}</span><span>{user?.username || 'Inventory user'}</span></div>
          <button className="icon-button logout-button" type="button" onClick={signOut} aria-label="Sign out" title="Sign out"><LogOut size={18} /></button>
        </div>
      </header>

      <section className="page-content">
        <div className="page-heading">
          <div>
            <p className="eyebrow">INVENTORY / OVERVIEW</p>
            <h1>Products <span className="heading-count">{products.length.toString().padStart(2, '0')}</span></h1>
            <p className="page-subtitle">A clear view of what is on your shelves.</p>
          </div>
          <button className="button button-primary add-button" type="button" onClick={() => setDialog({ product: null })}>
            <Plus size={18} /> <span>Add product</span>
          </button>
        </div>

        <div className="metrics-row">
          <div className="metric"><span className="metric-icon"><PackagePlus size={18} /></span><div><span className="metric-label">PRODUCTS LISTED</span><strong>{products.length}</strong></div></div>
          <div className="metric"><span className="metric-icon metric-icon-mint"><ArrowDownUp size={18} /></span><div><span className="metric-label">TOTAL UNITS</span><strong>{totalUnits.toLocaleString()}</strong></div></div>
          <div className="metric-note"><span className="status-dot" /> API connection <strong>Ready</strong></div>
        </div>

        <section className="inventory-section" aria-labelledby="inventory-title">
          <div className="inventory-toolbar">
            <div><h2 id="inventory-title">All products</h2><span className="result-count">{visibleProducts.length} records</span></div>
            <div className="table-tools">
              <label className="search-field"><Search size={17} /><input value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Find a product" aria-label="Search products" /></label>
              <label className="sort-field"><span className="sr-only">Sort products</span><select value={sort} onChange={(event) => setSort(event.target.value)}><option value="newest">Recently added</option><option value="name">Name A to Z</option></select></label>
            </div>
          </div>

          {error && <div className="notice-error"><CircleAlert size={17} /><span>{error}</span><button type="button" onClick={() => setError('')} aria-label="Dismiss error"><X size={16} /></button></div>}
          <div className="table-scroll">
            <table>
              <thead><tr><th>Product</th><th>Description</th><th className="align-right">Price</th><th className="align-right">Quantity</th><th>Created</th><th><span className="sr-only">Actions</span></th></tr></thead>
              <tbody>
                {loading ? <tr><td className="table-state" colSpan="6"><LoaderCircle className="spin" size={20} /> Loading inventory</td></tr> : visibleProducts.length === 0 ? (
                  <tr><td className="table-state empty-state" colSpan="6"><span className="empty-mark"><Box size={25} /></span><strong>{query ? 'No matching products' : 'Nothing on the shelves yet'}</strong><span>{query ? 'Try another search term.' : 'Add your first product to get the inventory moving.'}</span>{!query && <button className="text-button" type="button" onClick={() => setDialog({ product: null })}>Add a product <ArrowRight size={15} /></button>}</td></tr>
                ) : visibleProducts.map((product, index) => (
                  <tr key={product.id} className="product-row" style={{ '--row-index': index }}>
                    <td><div className="product-name"><span className="product-symbol"><Box size={17} /></span><strong>{product.product_name}</strong></div></td>
                    <td className="description-cell">{product.description || <span className="muted">No description</span>}</td>
                    <td className="align-right price-cell">{money(product.price)}</td>
                    <td className="align-right"><span className={`quantity-pill ${Number(product.quantity) < 5 ? 'quantity-low' : ''}`}>{product.quantity}</span></td>
                    <td className="date-cell">{product.created_at ? new Date(product.created_at.replace(' ', 'T') + (product.created_at.includes('Z') ? '' : 'Z')).toLocaleDateString() : '—'}</td>
                    <td><div className="row-actions"><button className="icon-button" type="button" title="Edit product" aria-label={`Edit ${product.product_name}`} onClick={() => setDialog({ product })}><Pencil size={16} /></button><button className="icon-button danger-icon" type="button" title="Delete product" aria-label={`Delete ${product.product_name}`} onClick={() => deleteProduct(product)}><Trash2 size={16} /></button></div></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <footer className="table-footer"><span><Check size={15} /> Changes sync directly with your API</span><span>Showing {visibleProducts.length} of {products.length}</span></footer>
        </section>
      </section>

      {dialog && <div className="modal-backdrop" onMouseDown={(event) => { if (event.target === event.currentTarget) setDialog(null); }}>
        <section className="product-dialog" role="dialog" aria-modal="true" aria-labelledby="dialog-title">
          <header className="dialog-heading"><div><p className="eyebrow">INVENTORY / ITEM</p><h2 id="dialog-title">{dialog.product ? 'Edit product' : 'Add product'}</h2></div><button className="icon-button" type="button" aria-label="Close" onClick={() => setDialog(null)}><X size={19} /></button></header>
          <form onSubmit={saveProduct}>
            <label htmlFor="product_name">Product name</label><input id="product_name" name="product_name" maxLength="100" required defaultValue={dialog.product?.product_name || ''} autoFocus />
            <label htmlFor="description">Description <span className="optional">OPTIONAL</span></label><textarea id="description" name="description" rows="3" defaultValue={dialog.product?.description || ''} />
            <div className="form-grid"><div><label htmlFor="price">Price</label><div className="input-prefix"><span>$</span><input id="price" name="price" type="number" min="0" max="99999999.99" step="0.01" required defaultValue={dialog.product?.price ?? ''} /></div></div><div><label htmlFor="quantity">Quantity</label><input id="quantity" name="quantity" type="number" min="0" step="1" required defaultValue={dialog.product?.quantity ?? ''} /></div></div>
            <div className="dialog-actions"><button className="button button-quiet" type="button" onClick={() => setDialog(null)}>Cancel</button><button className="button button-primary" type="submit" disabled={saving}>{saving ? <LoaderCircle className="spin" size={17} /> : <Check size={17} />}{dialog.product ? 'Save changes' : 'Create product'}</button></div>
          </form>
        </section>
      </div>}
    </main>
  );
}

createRoot(document.getElementById('root')).render(<React.StrictMode><App /></React.StrictMode>);
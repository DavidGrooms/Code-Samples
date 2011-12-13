class SalesCallsController < ApplicationController
  
  before_filter :authorize
  protect_from_forgery :only => [:update, :delete, :create]
  auto_complete_for :client, :company
  
 def callboard
    @agent = session[:cid]
    @clients = Client.paginate :page => params[:page], :per_page => 20, :order => 'id DESC'
    @calls = SalesCall.paginate :page => params[:page], :per_page => 20, :order => 'id DESC'
    @contacts = SalesContact.find(:all)
  end
  
  def show_call
    @call_id = params[:id]
    @sales_call = SalesCall.find(:first, :conditions => ['id = ?', @call_id])
    @contact = SalesContact.find(:first, :conditions => ['id = ?', @sales_call.contact_id])
    render :partial => 'show_call', :object => @sales_call do |page|
      page.replace 'calllist'
    end
  end
  
  def sales_call
    @contact_id = params[:id]
    @contact = SalesContact.find(:first, :conditions => ['id = ?', @contact_id])
    @sales_call = SalesCall.new
    @sales_call.agent_id = session[:cid]
    render :partial => 'sales_call', :object => @sales_call do |page|
      page.replace 'calllist'
    end
  end
  
  def delete_call
    @sales_call = SalesCall.find(params[:id])
    @sales_call.destroy
    @calls = SalesCall.paginate :page => params[:page], :per_page => 20, :order => 'id DESC'
    render :partial => 'call_list', :object => @sales_call do |page|
      page.replace 'calllist'
    end
  end
  
  def save_call
    @contact_id = params[:id]
    @agent = params[:aid]
    @sales_contact = SalesContact.find(:first, :conditions => ['id = ?', @contact_id])
    @sales_call = SalesCall.new(params[:sales_call])
    @sales_call.contact_id = @contact_id
    @sales_call.media = params['post'][:media]
    @sales_call.call_type = params['post'][:call_type]
    @sales_call.outcome = params['post'][:outcome]
    @sales_call.cid = @sales_contact.cid
    @sales_call.company = @sales_contact.company
    @sales_call.firstname = @sales_contact.firstname
    @sales_call.lastname = @sales_contact.lastname
    @sales_call.email = @sales_contact.email
    @sales_call.phone = @sales_contact.phone
    @sales_call.fax = @sales_contact.fax
    @sales_call.agent_id = @agent
    @sales_call.save!
    if params[:cid].nil?
      @calls = SalesCall.paginate :page => params[:page], :per_page => 20, :order => 'id DESC'
      render :partial => 'call_list', :object => @calls do |page|
        page.replace 'calllist'
      end
    else
      redirect_to :controller => :purchases, :action => :new_purchase, :id => params[:cid]
    end
  end
  
  def client_email
    @emails = Client.find(:all, :conditions => [ 'LOWER(email) LIKE ?', '%' + params[:getcdata][:email].downcase + '%' ], 
      :order => 'company ASC',
      :limit => 20)
    render :layout => false
  end  
  
  def client_company
    @companies = Client.find(:all, :conditions => [ 'LOWER(company) LIKE ?', '%' + params[:getcdata][:company].downcase + '%' ], 
      :order => 'company ASC',
      :limit => 20)
    render :layout => false
  end
  
  def getcdata
    @sales_contact = SalesContact.new
    @getcdata = params[:getcdata]
    @gemail = @getcdata["email"]
    @gcompany = @getcdata["company"]
    if @gemail
      @client = Client.find_by_email(@gemail)
    end
    if @gcompany
      @client = Client.find_by_company(@gcompany)
    end
    if @client
      @cid = @client.id
      @sales_contact.agent_id = session[:cid]
      @sales_contact.cid = @client.id
      @sales_contact.email = @client.email
      @sales_contact.company = @client.company
      @sales_contact.firstname = @client.firstname
      @sales_contact.lastname = @client.lastname
      @sales_contact.phone = @client.phone
      @sales_contact.fax = @client.fax
      @sales_contact.address1 = @client.mailing_address
      @sales_contact.address2 = @client.mailing_address2
      @sales_contact.city = @client.mailing_city
      @sales_contact.state = @client.mailing_state
      @sales_contact.zipcode = @client.mailing_zip
      @sales_contact.country = @client.mailing_country
    else
      @sales_contact.agent_id = session[:cid]
      @sales_contact.company = "Not found"
    end
    render :partial => 'contact_info', :object => @sales_contact do |page|
      page.replace 'contactlist'
    end
  end
  
  def lookup_contact
    @client = Client.find(:first, :conditions => ['email = ?', params[:email]])
    unless @client.nil?
      @sales_contact = SalesContact.find(:first, :conditions => ['id = ?', params[:id]])
      @sales_contact.cid = @client.id
      @sales_contact.save!
      render :partial => 'contact', :object => @sales_contact do |page|
        page.replace 'contactlist'
      end
    else
      render :partial => 'contact', :object => @sales_contact do |page|
        page.replace 'contactlist'
      end
    end
  end
  
  def update_contact
    @sales_call = SalesCall.find(params[:id])
    @sales_call.update_attributes(params[:sales_call])
    render :partial => 'edit_contact', :object => @sales_contact do |page|
      page.replace 'contactlist'
    end
  end
  
  def edit_contact
    @id = params[:id]
    @sales_contact = SalesContact.find(:first, :conditions => ['id = ?', @id])
    render :partial => 'edit_contact', :object => @sales_contact do |page|
      page.replace 'contactlist'
    end
  end
  
  def delete_contact
    @sales_contact = SalesContact.find(:first, :conditions => ['id = ?', params[:id]])
    @sales_contact.destroy
    @contacts = SalesContact.find(:all)
    render :partial => 'contacts', :object => @sales_contact do |page|
        page.replace 'contactlist'
      end
  end
  
  def create_contact
    @sales_contact = SalesContact.new(params[:sales_contact])
    @sales_contact.save!
    render :partial => 'contact', :object => @sales_contact do |page|
      page.replace 'contactlist'
    end
  end
  
  def new_client
    @sales_contact = SalesContact.new
    @sales_contact.agent_id = session[:cid]
    render :partial => 'new_client', :object => @sales_contact do |page|
      page.replace 'contactlist'
    end
  end
  
  def new_contact
    @sales_contact = SalesContact.new
    unless params[:cid].nil?
      @client = Client.find(:first, :conditions => ['id = ?', params[:cid]])
        if @client
          @cid = @client.id
          @sales_contact.agent_id = session[:cid]
          @sales_contact.cid = @client.id
          @sales_contact.email = @client.email
          @sales_contact.company = @client.company
          @sales_contact.firstname = @client.firstname
          @sales_contact.lastname = @client.lastname
          @sales_contact.phone = @client.phone
          @sales_contact.fax = @client.fax
          @sales_contact.address1 = @client.mailing_address
          @sales_contact.address2 = @client.mailing_address2
          @sales_contact.city = @client.mailing_city
          @sales_contact.state = @client.mailing_state
          @sales_contact.zipcode = @client.mailing_zip
          @sales_contact.country = @client.mailing_country
      end
    end
    render :partial => 'new_contact', :object => @sales_contact do |page|
      page.replace 'contactlist'
    end
  end
  
  def contact
    @sales_contact = SalesContact.find(:first, :conditions => ['id = ?', params[:id]])
    render :partial => 'contact', :object => @sales_contact do |page|
      page.replace 'contactlist'
    end
  end
  
  def contacts
    @contacts = SalesContact.find(:all)
    render :partial => 'contacts', :object => @sales_contact do |page|
      page.replace 'contactlist'
    end
  end
  
  def callboard
    @agent = params[:id]
    @clients = Client.paginate :page => params[:page], :per_page => 20, :order => 'id DESC'
    @calls = SalesCall.paginate :page => params[:page], :per_page => 20, :order => 'id DESC'
    @contacts = SalesContact.find(:all)
  end
  
  def searched_calls
    @agent_id = params[:aid]
    @get = params[:search]
    @by_company = SalesCall.find(:all, :conditions => ['cid = ? or company like ?', @get.to_i, @get.to_s], :order => 'id DESC')
    @calls = @by_company.paginate :page => params[:page], :per_page => 20, :order => 'id DESC'
    render :partial => 'call_list', :object => @contacts do |page|
      page.replace 'calllist'
    end
  end
  
  def my_calls
    @agent_id = params[:aid]
    @my_calls = SalesCall.find(:all, :conditions => ['agent_id = ? ', @agent_id], :order => 'id DESC')
    @calls = @my_calls.paginate :page => params[:page], :per_page => 20, :order => 'id DESC'
    render :partial => 'call_list', :object => @calls do |page|
      page.replace 'calllist'
    end
  end
  
  def call_list
    @cid = params[:id]
    
    @calls = SalesCall.paginate :page => params[:page], :per_page => 20, :order => 'id DESC'
    render :partial => 'call_list', :object => @calls do |page|
      page.replace 'calllist'
    end
  end
  
#_______________________________________________  
  
  # GET /sales_calls
  # GET /sales_calls.xml
  def index
    @sales_calls = SalesCall.all

    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @sales_calls }
    end
  end

  # GET /sales_calls/1
  # GET /sales_calls/1.xml
  def show
    @sales_call = SalesCall.find(params[:id])

    respond_to do |format|
      format.html # show.html.erb
      format.xml  { render :xml => @sales_call }
    end
  end

  # GET /sales_calls/new
  # GET /sales_calls/new.xml
  def new
    @sales_call = SalesCall.new

    respond_to do |format|
      format.html # new.html.erb
      format.xml  { render :xml => @sales_call }
    end
  end

  # GET /sales_calls/1/edit
  def edit
    @sales_call = SalesCall.find(params[:id])
  end

  # POST /sales_calls
  # POST /sales_calls.xml
  def create
    @sales_call = SalesCall.new(params[:sales_call])

    respond_to do |format|
      if @sales_call.save
        flash[:notice] = 'SalesCall was successfully created.'
        format.html { redirect_to(@sales_call) }
        format.xml  { render :xml => @sales_call, :status => :created, :location => @sales_call }
      else
        format.html { render :action => "new" }
        format.xml  { render :xml => @sales_call.errors, :status => :unprocessable_entity }
      end
    end
  end

  # PUT /sales_calls/1
  # PUT /sales_calls/1.xml
  def update
    @sales_call = SalesCall.find(params[:id])

    respond_to do |format|
      if @sales_call.update_attributes(params[:sales_call])
        flash[:notice] = 'SalesCall was successfully updated.'
        format.html { redirect_to(@sales_call) }
        format.xml  { head :ok }
      else
        format.html { render :action => "edit" }
        format.xml  { render :xml => @sales_call.errors, :status => :unprocessable_entity }
      end
    end
  end

  # DELETE /sales_calls/1
  # DELETE /sales_calls/1.xml
  def destroy
    @sales_call = SalesCall.find(params[:id])
    @sales_call.destroy

    respond_to do |format|
      format.html { redirect_to(sales_calls_url) }
      format.xml  { head :ok }
    end
  end
  
    protected
  def authorize
    if session[:user_id].nil?
      flash[:notice] = "Please log in"
      redirect_to :controller => 'admin', :action => 'logout'
    end
  end
  
  def admin_only(referer)
    session[:referer] = referer
    flash[:notice] = "You must be an Administrator."
    redirect_to :controller => 'admin', :action => 'logout'
  end
end

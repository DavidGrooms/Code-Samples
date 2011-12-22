class UsersController < ApplicationController
  
  before_filter :authorize, :except => [:login, :contact]

  def contact
    if params[:id] = 'supplier_contact1'
      @commit = Commitment.find(:first, :conditions => ['ad_ready = ?', params[:ad]])
      redirect_to :controller => :leads, :action => :contact_supplier, :id => 1, :adid => params[:ad], :comid => @commit.id
    end
  end

  # GET /users
  # GET /users.xml
  def index
    @users = User.paginate :page => params[:page], :per_page => 45, :order => 'name'

    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @users }
    end
  end

  # GET /users/1
  # GET /users/1.xml
  def show
    @client = Client.find(:first, :select => 'id, email', :conditions => ['id = ?', params[:id].to_i])
    @user = User.find(:first, :conditions => ['name = ?', @client.email], :order => 'id DESC')
    respond_to do |format|
      format.html # show.html.erb
      format.xml  { render :xml => @user }
    end
  end

  # GET /users/new
  # GET /users/new.xml
  def new
    @user = User.new

    respond_to do |format|
      format.html # new.html.erb
      format.xml  { render :xml => @user }
    end
  end

  # GET /users/1/edit
  def edit
    @user = User.find(params[:id])
  end

  # POST /users
  # POST /users.xml
  def create
    @user = User.new(params[:user])

    respond_to do |format|
      if @user.save
        flash[:notice] = "New Client #{@user.name} was successfully created."
        format.html { redirect_to(:action => 'index') }
        format.xml  { render :xml => @user, :status => :created, :location => @user }
      else
        format.html { render :action => "new" }
        format.xml  { render :xml => @user.errors, :status => :unprocessable_entity }
      end
    end
  end

  # PUT /users/1
  # PUT /users/1.xml
  def update
    @user = User.find(params[:id])
    @user.attributes = {:name => params[:user][:name], :role => params[:user][:role]}
    unless @user.nil?
              @salt = self.object_id.to_s + rand.to_s
              @string_to_hash = params[:user][:sbpass] + "smart" + @salt 
              @hashed_pass = Digest::SHA1.hexdigest(@string_to_hash)
              @user.attributes = {:hashed_password => @hashed_pass, :salt => @salt, :sbpass => params[:user][:sbpass]}
    end
    respond_to do |format|
      if @user.save!
        flash[:notice] = "Client Account #{@user.name} was successfully updated."
        format.html { redirect_to(:action => 'index') }
        format.xml  { head :ok }
      else
        format.html { render :action => "edit" }
        format.xml  { render :xml => @user.errors, :status => :unprocessable_entity }
      end
    end
  end

  # DELETE /users/1
  # DELETE /users/1.xml
  def destroy
    @user = User.find(params[:id])
    @user.destroy

    respond_to do |format|
      format.html { redirect_to(users_url) }
      format.xml  { head :ok }
    end
  end
  
  def search 
    @users = User.search params[:search]
  end 
  
  protected
  
  def authorize
    unless User.find_by_id(session[:user_id])
      session[:referer] = "/users"
      flash[:notice] = "Please log in"
      redirect_to :controller => 'admin', :action => 'login'
    else 
     unless params[:b] = 'request' 
      unless User.find_by_id(session[:user_id]).role == 'Admin'
        session[:referer] = "/users"
        flash[:notice] = "You must be an Administrator."
        redirect_to :controller => 'admin', :action => 'login'
      end
     end
    end
  end
  
end

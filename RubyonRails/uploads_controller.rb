class UploadsController < ApplicationController
  
  before_filter :authorize

  def client_upload
    @upload = Upload.new
    @comid = params[:comid]
    @commit = Commitment.find_by_id(@comid)
    @upload.comid = @comid
    @upload.cid = @commit.cid
    @source = 'Client'
    @upload = Upload.find(:first, :conditions => ['comid = ? and art_type = ?', @commit.id, 's'])
    unless @upload.nil?
      @branded_image_link = "<img src='http://backend.xxxx.com"  + @upload.public_filename  + "' width='125' height='160' />"
    else
      @branded_image_link = 'No Branded Image<br />'
    end
    @upload = Upload.find(:first, :conditions => ['comid = ? and art_type = ?', @commit.id, 'e'])
    unless @upload.nil?
      @unbranded_image_link = "<img src='http://backend.xxxx.com"  + @upload.public_filename  + "' width='125' height='160' />"
    else
      @unbranded_image_link = 'No Unbranded Image<br />'
    end
  end
  
  def product_art
    @upload = Upload.new
    @comid = params[:comid]
    @commit = Commitment.find_by_id(@comid)
    @product = Product.find_by_id(@commit.product_id)
    @pid  = params[:id]
    @upload.comid = @comid
    @upload.cid = @commit.cid
    @source = 'Client'
    @upload = Upload.find(:first, :conditions => ['comid = ? and art_type = ?', @commit.id, 'r'])
    unless @upload.nil?
    @branded_image_link = "<img src='http://backend.xxxx.com"  + @upload.public_filename  + "' width='125' height='160' />"
    else
      @branded_image_link = 'No Branded Image<br />'
    end
    @upload = Upload.find(:first, :conditions => ['comid = ? and art_type = ?', @commit.id, 'u'])
    unless @upload.nil?
    @unbranded_image_link = "<img src='http://backend.xxxx.com"  + @upload.public_filename  + "' width='125' height='160' />"
    else
      @unbranded_image_link = 'No Unbranded Image<br />'
    end
  end
  
  def new
    @upload = Upload.new
    @commit = Commitment.new
    @comid = params[:comid]
    @commit = Commitment.find_by_id(@comid)
    @upload.comid = @comid
    @upload.cid = @commit.cid
    @source = 'Admin'
  end
  
  # POST /uploads
  # POST /uploads.xml
  def create
    @upload = Upload.new(params[:upload])
    @art = @upload.art_type
    @cid = @upload.cid
    @comid = @upload.comid
    @upload.filename = @comid.to_s+"-"+@art.to_s+Time.now.strftime("-%m-%d-%H-%M")+@upload.filename
    if @upload.save
      unless @art == "p"
        @tid = @upload.id + 1
        @thumb = Upload.find(:first, :conditions => ['id=?', @tid])
        @thumb.attributes = {:cid => @cid, :comid => @comid}
        @thumb.save!
      end
      @commit = Commitment.find_by_id(@comid)
      if @art == 's'
        @commit.attributes = {:supplierart => 'x'}
      end
      if @art == 'e'
        @commit.attributes = {:enduserart => 'x'}
      end
      if @art == 'p'
        @commit.attributes = {:supplierpdf => 'x'}
      end
      if @art == 'r'
        @commit.attributes = {:supplierart => 'x'}
        @product = Product.find_by_id(params[:id])
        @product.retail_art_id = @upload.id
        @product.save!
      end
      if @art == 'u'
        @commit.attributes = {:enduserart => 'x'}
        @product = Product.find_by_id(params[:id])
        @product.unbranded_art_id = @upload.id
        @product.save!
      end
      @commit.save!
      
      if @art == 's' or @art == 'e' or @art == 'p'
        if params['created_by'] == "Admin"
          redirect_to :action => 'new', :comid => @comid, :art_type => @art
        else
          redirect_to :action => 'client_upload', :id => 1, :comid => @comid, :art_type => @art
        end
      end
       if @art == 'r' or @art == 'u'
          redirect_to :action => 'product_art', :id => @product.id, :comid => @comid, :art_type => @art
      end

    else
      flash[:notice] = 'Error: File not Saved - Image files only - less than 3.megabytes - no pdf'
      respond_to do |format|
        format.html { redirect_to :action => 'new', :comid => @comid, :art_type => @art }
        format.xml  { render :xml => @upload.errors, :status => :unprocessable_entity }
      end
    end
  end  
  
#_____________________________________________________________

  # GET /uploads
  # GET /uploads.xml
  def index
    @uploads = Upload.paginate :page => params[:page], :per_page => 25, :order => 'comid DESC'
    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @uploads }
    end
  end

  # GET /uploads/1
  # GET /uploads/1.xml
  def show
    @upload = Upload.find(params[:id])

    respond_to do |format|
      format.html # show.html.erb
      format.xml  { render :xml => @upload }
    end
  end

  # GET /uploads/1/edit
  def edit
    @upload = Upload.find(params[:id])
  end

  # PUT /uploads/1
  # PUT /uploads/1.xml
  def update
    @upload = Upload.find(params[:id])

    respond_to do |format|
      if @upload.update_attributes(params[:upload])
        flash[:notice] = 'Upload was successfully updated.'
        format.html { redirect_to(@upload) }
        format.xml  { head :ok }
      else
        format.html { render :action => "edit" }
        format.xml  { render :xml => @upload.errors, :status => :unprocessable_entity }
      end
    end
  end

  # DELETE /uploads/1
  # DELETE /uploads/1.xml
  def destroy
    @upload = Upload.find(params[:id])
    @upload.destroy

    respond_to do |format|
      format.html { redirect_to(uploads_url) }
      format.xml  { head :ok }
    end
  end
  
  def search 
    @uploads = Upload.search params[:search]
    @comid = @uploads.comid
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
